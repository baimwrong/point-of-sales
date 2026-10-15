<?php

namespace App\Helpers;

use App\Models\Setting;

/**
 * PosHelper
 *
 * Collection of static utility methods used throughout the POS application.
 */
class PosHelper
{
    /**
     * Format a numeric amount into a human-readable currency string.
     *
     * @param  float  $amount
     * @return string  e.g. "Rp 1.250.000"
     */
    public static function formatCurrency(float $amount): string
    {
        $symbol = Setting::get('currency_symbol', 'Rp');
        return $symbol . ' ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Generate a unique invoice number for a sale.
     *
     * Format: {PREFIX}-{YYYYMMDD}-{NNNN}
     *
     * @return string  e.g. "INV-20260101-0001"
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV');
        $date   = now()->format('Ymd');
        $base   = $prefix . '-' . $date . '-';

        $lastNumber = \App\Models\Sale::withTrashed()
            ->where('invoice_number', 'like', $base . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = 1;
        if ($lastNumber && strlen($lastNumber) > strlen($base)) {
            $lastSeq = (int) substr($lastNumber, strlen($base));
            $next = $lastSeq + 1;
        }

        $candidate = $base . str_pad($next, 4, '0', STR_PAD_LEFT);
        while (\App\Models\Sale::withTrashed()->where('invoice_number', $candidate)->exists()) {
            $next++;
            $candidate = $base . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Generate a unique purchase order number.
     *
     * Format: {PREFIX}-{YYYYMMDD}-{NNNN}
     *
     * @return string  e.g. "PO-20260101-0001"
     */
    public static function generatePurchaseNumber(): string
    {
        $prefix = Setting::get('purchase_prefix', 'PO');
        $date   = now()->format('Ymd');
        $base   = $prefix . '-' . $date . '-';

        $lastNumber = \App\Models\Purchase::withTrashed()
            ->where('reference_number', 'like', $base . '%')
            ->orderByDesc('reference_number')
            ->value('reference_number');

        $next = 1;
        if ($lastNumber && strlen($lastNumber) > strlen($base)) {
            $lastSeq = (int) substr($lastNumber, strlen($base));
            $next = $lastSeq + 1;
        }

        $candidate = $base . str_pad($next, 4, '0', STR_PAD_LEFT);
        while (\App\Models\Purchase::withTrashed()->where('reference_number', $candidate)->exists()) {
            $next++;
            $candidate = $base . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Generate a unique return / refund number.
     *
     * Format: {PREFIX}-{YYYYMMDD}-{NNNN}
     *
     * @return string  e.g. "RTN-20260101-0001"
     */
    public static function generateReturnNumber(): string
    {
        $prefix = Setting::get('return_prefix', 'RTN');
        $date   = now()->format('Ymd');
        $base   = $prefix . '-' . $date . '-';

        $lastNumber = \App\Models\SaleReturn::withTrashed()
            ->where('return_number', 'like', $base . '%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $next = 1;
        if ($lastNumber && strlen($lastNumber) > strlen($base)) {
            $lastSeq = (int) substr($lastNumber, strlen($base));
            $next = $lastSeq + 1;
        }

        $candidate = $base . str_pad($next, 4, '0', STR_PAD_LEFT);
        while (\App\Models\SaleReturn::withTrashed()->where('return_number', $candidate)->exists()) {
            $next++;
            $candidate = $base . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Retrieve an application setting value by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    /**
     * Calculate the tax amount for a given subtotal and tax rate.
     *
     * @param  float  $subtotal
     * @param  float  $taxRate  Percentage value (e.g. 10 for 10 %).
     * @return float
     */
    public static function calculateTax(float $subtotal, float $taxRate): float
    {
        return round($subtotal * ($taxRate / 100), 2);
    }

    /**
     * Calculate loyalty points to be awarded for a sale amount.
     *
     * @param  float  $amount
     * @return int
     */
    public static function calculateLoyaltyPoints(float $amount): int
    {
        $pointsPerUnit = (int) Setting::get('loyalty_points_per_unit', 1);
        $unitAmount    = (float) Setting::get('loyalty_unit_amount', 10000);

        if ($unitAmount <= 0) {
            return 0;
        }

        return (int) floor($amount / $unitAmount) * $pointsPerUnit;
    }

    /**
     * Format bytes into a human-readable string.
     *
     * @param  int  $bytes
     * @param  int  $precision
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * Generate a URL-friendly slug from a string.
     *
     * @param  string  $text
     * @return string
     */
    public static function makeSlug(string $text): string
    {
        return \Illuminate\Support\Str::slug($text);
    }

    /**
     * Sanitize a value for safe CSV export to prevent Formula Injection (CWE-1236).
     *
     * @param  mixed  $value
     * @return string
     */
    public static function sanitizeCsvValue(mixed $value): string
    {
        $str = (string) $value;
        if (strlen($str) > 0 && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $str;
        }
        return $str;
    }
}
