<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function updateStore(Request $request)
    {
        $data = $request->validate([
            'store_name'    => ['required', 'string', 'max:100'],
            'store_address' => ['nullable', 'string', 'max:300'],
            'store_phone'   => ['nullable', 'string', 'max:20'],
            'store_email'   => ['nullable', 'email', 'max:100'],
        ]);
        foreach ($data as $key => $value) Setting::set($key, $value);
        Setting::clearCache();
        ActivityLog::log('settings', 'Updated store information settings');
        return back()->with('success', 'Store information updated.');
    }

    public function updateTax(Request $request)
    {
        $data = $request->validate([
            'tax_rate'        => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_name'        => ['required', 'string', 'max:20'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'currency_code'   => ['required', 'string', 'max:5'],
        ]);
        foreach ($data as $key => $value) Setting::set($key, $value);
        Setting::clearCache();
        ActivityLog::log('settings', 'Updated tax & currency settings');
        return back()->with('success', 'Tax & currency settings updated.');
    }

    public function updateReceipt(Request $request)
    {
        $data = $request->validate([
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'receipt_prefix' => ['required', 'string', 'max:10'],
            'purchase_prefix'=> ['required', 'string', 'max:10'],
            'return_prefix'  => ['required', 'string', 'max:10'],
            'receipt_footer' => ['nullable', 'string', 'max:300'],
            'loyalty_rate'   => ['nullable', 'numeric', 'min:0'],
        ]);
        foreach ($data as $key => $value) Setting::set($key, $value);
        Setting::clearCache();
        ActivityLog::log('settings', 'Updated receipt & numbering settings');
        return back()->with('success', 'Receipt settings updated.');
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token', '_method') as $key => $value) {
            Setting::set($key, $value);
        }
        Setting::clearCache();
        return back()->with('success', 'Settings updated.');
    }
}
