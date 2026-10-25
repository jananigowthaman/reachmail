<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('contacts')->latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|max:20',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category = Category::create($validated);

        ActivityLog::log('category_created', "Created list category '{$category->name}'", $category);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|max:20',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        ActivityLog::log('category_updated', "Updated category '{$category->name}'", $category);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $name = $category->name;
        // Detach contacts without deleting them
        $category->contacts()->detach();
        $category->delete();

        ActivityLog::log('category_deleted', "Deleted category '{$name}'");

        return redirect()->route('categories.index')->with('success', "Category '{$name}' deleted. Associated contacts remain safe.");
    }
}
