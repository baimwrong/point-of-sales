<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityLog;
use App\Http\Requests\CustomerRequest;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::latest();
        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('name','ilike',"%$s%")->orWhere('email','ilike',"%$s%")->orWhere('phone','ilike',"%$s%"));
        }
        $customers = $query->paginate(15)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create() { return view('customers.create'); }

    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        ActivityLog::log('create', "Created customer: {$customer->name}", $customer);
        return redirect()->route('customers.index')->with('success', "Customer \"{$customer->name}\" created.");
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        $sales    = $customer->sales()->latest()->paginate(10);
        $totalSpent = $customer->sales()->where('status', 'completed')->sum('total_amount');
        return view('customers.show', compact('customer', 'sales', 'totalSpent'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->validated());
        ActivityLog::log('update', "Updated customer: {$customer->name}", $customer);
        return redirect()->route('customers.index')->with('success', "Customer \"{$customer->name}\" updated.");
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $name = $customer->name;
        $customer->delete();
        ActivityLog::log('delete', "Deleted customer: {$name}");
        return redirect()->route('customers.index')->with('success', "Customer \"{$name}\" deleted.");
    }

    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $customers = Customer::active()
            ->where(fn($q) => $q->where('name','ilike',"%$term%")->orWhere('phone','ilike',"%$term%")->orWhere('email','ilike',"%$term%"))
            ->limit(15)
            ->get(['id','name','email','phone','loyalty_points']);
        return response()->json($customers);
    }
}
