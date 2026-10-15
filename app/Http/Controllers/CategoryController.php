<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products')->with('parent');
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->active()->orderBy('name')->get();
        return view('categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);
        $data['slug']      = Str::slug($data['name']) . '-' . Str::random(5);
        $data['is_active'] = $request->boolean('is_active', true);
        $cat = Category::create($data);
        ActivityLog::log('create', "Created category: {$cat->name}", $cat);
        return redirect()->route('categories.index')->with('success', "Category \"{$cat->name}\" created.");
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $parents  = Category::whereNull('parent_id')->where('id', '!=', $id)->active()->orderBy('name')->get();
        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'parent_id'   => ['nullable', 'exists:categories,id', \Illuminate\Validation\Rule::notIn([$id])],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $category->update($data);
        ActivityLog::log('update', "Updated category: {$category->name}", $category);
        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" updated.");
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated products.');
        }
        $name = $category->name;
        $category->delete();
        ActivityLog::log('delete', "Deleted category: {$name}");
        return redirect()->route('categories.index')->with('success', "Category \"{$name}\" deleted.");
    }
}
