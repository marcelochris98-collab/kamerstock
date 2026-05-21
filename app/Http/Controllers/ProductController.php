<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  // 1. Ajoute Request $request dans les paramètres de la fonction index
public function index(Request $request)
{
    $query = Product::with(['category', 'supplier'])
                    ->where('is_active', true)
                    ->orderBy('name');

    // 2. Remplace request('search') par $request->search
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('reference', 'like', '%' . $request->search . '%');
        });
    }

    // 3. Remplace request('category_id') par $request->category_id
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    $products   = $query->paginate(20);
    $categories = Category::orderBy('name')->get();

    return view('products.index', compact('products', 'categories'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'reference'       => 'required|string|max:50|unique:products,reference',
            'category_id'     => 'nullable|exists:categories,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'unit'            => 'required|in:piece,metre,kg,litre,boite,sachet',
            'price_buy'       => 'required|numeric|min:0',
            'price_sell'      => 'required|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'reference.unique'   => 'Cette référence existe déjà.',
            'price_sell.min'     => 'Le prix de vente ne peut pas être négatif.',
            'price_buy.min'      => 'Le prix d\'achat ne peut pas être négatif.',
        ]);

        // Validation métier
        if ($request->price_sell < $request->price_buy) {
            return back()->withInput()->withErrors([
                'price_sell' => ' Le prix de vente doit être supérieur ou égal au prix d\'achat.'
            ]);
        }

        $product = Product::create($request->all());

        ActivityLog::record('product.create', "Produit créé : {$product->name}");

        return redirect()->route('products.index')
                         ->with('success', " Produit {$product->name} créé !");
    }
    public function create()
{
    // On récupère les catégories et fournisseurs pour alimenter les listes déroulantes du formulaire
    $categories = Category::orderBy('name')->get();
    $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();

    return view('products.create', compact('categories', 'suppliers'));
}

    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'stockMovements' => fn($q) => $q->latest()->limit(10)]);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'reference'       => 'required|string|max:50|unique:products,reference,' . $product->id,
            'category_id'     => 'nullable|exists:categories,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'unit'            => 'required|in:piece,metre,kg,litre,boite,sachet',
            'price_buy'       => 'required|numeric|min:0',
            'price_sell'      => 'required|numeric|min:0',
            'alert_threshold' => 'required|integer|min:0',
        ]);

        if ($request->price_sell < $request->price_buy) {
            return back()->withInput()->withErrors([
                'price_sell' => ' Le prix de vente doit être supérieur ou égal au prix d\'achat.'
            ]);
        }

        $product->update($request->all());

        ActivityLog::record('product.update', "Produit modifié : {$product->name}");

        return redirect()->route('products.index')
                         ->with('success', " Produit mis à jour !");
    }

    public function destroy(Product $product)
    {
        if ($product->saleDetails()->count() > 0) {
            return back()->withErrors([
                'error' => ' Impossible de supprimer — ce produit a des ventes associées.'
            ]);
        }

        $name = $product->name;
        $product->update(['is_active' => false]);

        ActivityLog::record('product.delete', "Produit désactivé : {$name}");

        return back()->with('success', " Produit désactivé !");
    }
}