<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('products')->orderBy('name')->paginate(20)->withQueryString();
        return view('units.index', compact('units'));
    }

    public function create() { return view('units.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:50', 'unique:units,name'],
            'abbreviation' => ['required', 'string', 'max:10'],
        ]);
        Unit::create($request->only('name', 'abbreviation'));
        return redirect()->route('units.index')->with('success', 'Unit created.');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $request->validate([
            'name'         => ['required', 'string', 'max:50', 'unique:units,name,' . $id],
            'abbreviation' => ['required', 'string', 'max:10'],
        ]);
        $unit->update($request->only('name', 'abbreviation'));
        return redirect()->route('units.index')->with('success', 'Unit updated.');
    }

    public function destroy($id)
    {
        $unit = Unit::withCount('products')->findOrFail($id);
        if ($unit->products_count > 0) {
            return back()->with('error', 'Cannot delete unit that is used by products.');
        }
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }
}
