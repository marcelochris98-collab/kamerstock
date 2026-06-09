<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
                               ->orderBy('name')
                               ->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.unique'   => 'Cette catégorie existe déjà.',
        ]);

        $category = Category::create($request->only('name', 'parent_id'));

        ActivityLog::record('category.create', "Catégorie créée : {$category->name}");

        return back()->with('success', " Catégorie créée !");
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);

        $category->update($request->only('name', 'parent_id'));

        ActivityLog::record('category.update', "Catégorie modifiée : {$category->name}");

        return back()->with('success', " Catégorie mise à jour !");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->withErrors([
                'error' => ' Impossible de supprimer — cette catégorie contient des produits.'
            ]);
        }

        $category->delete();
        ActivityLog::record('category.delete', "Catégorie supprimée : {$category->name}");

        return back()->with('success', " Catégorie supprimée !");
    }
    public function lookup(Request $request)
{
    $name = trim($request->name ?? '');

    $categories = Category::query()
        ->where('name', 'like', "%{$name}%")
        ->limit(5)
        ->get();

    return response()->json([
        'found' => $categories->isNotEmpty(),
        'categories' => $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        }),
    ]);
}
}