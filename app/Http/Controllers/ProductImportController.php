<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ActivityLog;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductImportController extends Controller
{
    /**
     * Show the bulk import UI.
     */
    public function show()
    {
        $categories = Category::active()->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('products.import', compact('categories', 'units'));
    }

    /**
     * Download sample CSV template compatible with Excel.
     */
    public function downloadTemplate()
    {
        $headers = [
            'name',
            'sku',
            'barcode',
            'category',
            'unit',
            'cost_price',
            'selling_price',
            'wholesale_price',
            'stock_quantity',
            'min_stock_alert',
            'description',
        ];

        $sampleRows = [
            [
                'Kopi Robusta Lampung 250g',
                'KOP-ROB-001',
                '899123456001',
                'Minuman',
                'pcs',
                '25000',
                '38000',
                '34000',
                '100',
                '10',
                'Kopi bubuk robusta murni asli Lampung sangrai dark roast',
            ],
            [
                'Minyak Kelapa Sawit 1L',
                'MGO-001',
                '899123456002',
                'Sembako',
                'pcs',
                '14000',
                '18500',
                '17000',
                '50',
                '5',
                'Minyak goreng kemasan pouch 1 liter higienis',
            ],
        ];

        $filename = 'product_import_template.csv';

        return response()->stream(function () use ($headers, $sampleRows) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens accented characters cleanly
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Process bulk import from Excel / CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        $file      = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $update    = $request->boolean('update_existing', false);

        try {
            $rows = SpreadsheetService::parse($file->getRealPath(), $extension);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error reading spreadsheet: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'Spreadsheet is empty or headers could not be found.');
        }

        $importedCount = 0;
        $updatedCount  = 0;
        $errors        = [];

        // Preload categories & units for fast lookup
        $categoriesMap = Category::all()->keyBy(fn($c) => strtolower(trim($c->name)));
        $unitsMap      = Unit::all()->keyBy(fn($u) => strtolower(trim($u->abbreviation)));

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Header is row 1

                // Extract fields
                $name = trim($row['name'] ?? $row['product_name'] ?? '');
                if ($name === '') {
                    $errors[] = "Row {$rowNumber}: Skipped - Product name is required.";
                    continue;
                }

                $sku = trim($row['sku'] ?? '');
                if ($sku === '') {
                    $sku = 'PRD-' . strtoupper(Str::random(6));
                }

                $barcode        = !empty($row['barcode']) ? trim($row['barcode']) : null;
                $costPrice      = isset($row['cost_price']) ? (float) str_replace([',', '.'], ['', '.'], $row['cost_price']) : 0;
                $sellingPrice   = isset($row['selling_price']) ? (float) str_replace([',', '.'], ['', '.'], $row['selling_price']) : 0;
                $wholesalePrice = !empty($row['wholesale_price']) ? (float) str_replace([',', '.'], ['', '.'], $row['wholesale_price']) : null;
                $stockQuantity  = isset($row['stock_quantity']) ? (int) $row['stock_quantity'] : 0;
                $minStockAlert  = isset($row['min_stock_alert']) ? (int) $row['min_stock_alert'] : 5;
                $description    = trim($row['description'] ?? '');

                // Match or create Category
                $catName = strtolower(trim($row['category'] ?? $row['category_name'] ?? ''));
                $categoryId = null;
                if ($catName !== '') {
                    if (isset($categoriesMap[$catName])) {
                        $categoryId = $categoriesMap[$catName]->id;
                    } else {
                        $newCat = Category::create([
                            'name'      => trim($row['category']),
                            'slug'      => Str::slug($row['category']) . '-' . Str::random(4),
                            'is_active' => true,
                        ]);
                        $categoriesMap[$catName] = $newCat;
                        $categoryId = $newCat->id;
                    }
                }

                // Match or create Unit
                $unitName = strtolower(trim($row['unit'] ?? $row['unit_name'] ?? 'pcs'));
                $unitId = null;
                if ($unitName !== '') {
                    if (isset($unitsMap[$unitName])) {
                        $unitId = $unitsMap[$unitName]->id;
                    } else {
                        $newUnit = Unit::create([
                            'name'         => ucfirst($unitName),
                            'abbreviation' => $unitName,
                        ]);
                        $unitsMap[$unitName] = $newUnit;
                        $unitId = $newUnit->id;
                    }
                }

                // Check for existing product with same SKU
                $existing = Product::where('sku', $sku)->first();

                if ($existing) {
                    if ($update) {
                        $existing->update([
                            'name'            => $name,
                            'barcode'         => $barcode,
                            'category_id'     => $categoryId ?: $existing->category_id,
                            'unit_id'         => $unitId ?: $existing->unit_id,
                            'cost_price'      => $costPrice,
                            'selling_price'   => $sellingPrice,
                            'wholesale_price' => $wholesalePrice,
                            'stock_quantity'  => $stockQuantity,
                            'min_stock_alert' => $minStockAlert,
                            'description'     => $description ?: $existing->description,
                        ]);
                        $updatedCount++;
                    } else {
                        $errors[] = "Row {$rowNumber}: Skipped - SKU \"{$sku}\" already exists (enable 'Update existing' to overwrite).";
                    }
                } else {
                    Product::create([
                        'name'            => $name,
                        'slug'            => Str::slug($name) . '-' . Str::random(5),
                        'sku'             => $sku,
                        'barcode'         => $barcode,
                        'category_id'     => $categoryId,
                        'unit_id'         => $unitId,
                        'cost_price'      => $costPrice,
                        'selling_price'   => $sellingPrice,
                        'wholesale_price' => $wholesalePrice,
                        'stock_quantity'  => $stockQuantity,
                        'min_stock_alert' => $minStockAlert,
                        'description'     => $description,
                        'track_stock'     => true,
                        'is_active'       => true,
                    ]);
                    $importedCount++;
                }
            }

            DB::commit();

            ActivityLog::log(
                'bulk_import',
                "Bulk imported {$importedCount} products, updated {$updatedCount} products via spreadsheet"
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed due to database error: ' . $e->getMessage());
        }

        $message = "Bulk Import Complete: {$importedCount} products added, {$updatedCount} updated.";
        if (!empty($errors)) {
            return redirect()->route('products.index')
                ->with('warning', $message . ' (Some rows had warnings - check notifications or details).')
                ->with('import_errors', $errors);
        }

        return redirect()->route('products.index')->with('success', $message);
    }

    /**
     * Export all products to CSV format for Excel.
     */
    public function export()
    {
        $products = Product::with(['category', 'unit'])->latest()->get();

        $filename = 'products_catalog_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, [
                'name',
                'sku',
                'barcode',
                'category',
                'unit',
                'cost_price',
                'selling_price',
                'wholesale_price',
                'stock_quantity',
                'min_stock_alert',
                'is_active',
                'description',
            ]);

            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->name,
                    $p->sku,
                    $p->barcode,
                    $p->category?->name ?? '',
                    $p->unit?->abbreviation ?? 'pcs',
                    $p->cost_price,
                    $p->selling_price,
                    $p->wholesale_price,
                    $p->stock_quantity,
                    $p->min_stock_alert,
                    $p->is_active ? 1 : 0,
                    $p->description,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
