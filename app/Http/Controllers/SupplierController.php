<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ActivityLog;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::latest();
        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('name','ilike',"%$s%")->orWhere('email','ilike',"%$s%")->orWhere('phone','ilike',"%$s%"));
        }
        $suppliers = $query->paginate(15)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.create'); }

    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());
        ActivityLog::log('create', "Created supplier: {$supplier->name}", $supplier);
        return redirect()->route('suppliers.index')->with('success', "Supplier \"{$supplier->name}\" created.");
    }

    public function show($id)
    {
        $supplier  = Supplier::findOrFail($id);
        $purchases = $supplier->purchases()->with('user')->latest()->paginate(10);
        return view('suppliers.show', compact('supplier', 'purchases'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->validated());
        ActivityLog::log('update', "Updated supplier: {$supplier->name}", $supplier);
        return redirect()->route('suppliers.index')->with('success', "Supplier \"{$supplier->name}\" updated.");
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $name = $supplier->name;
        $supplier->delete();
        ActivityLog::log('delete', "Deleted supplier: {$name}");
        return redirect()->route('suppliers.index')->with('success', "Supplier \"{$name}\" deleted.");
    }
}
