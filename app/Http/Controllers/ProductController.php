<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\AiService;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier'])
            ->where('is_active', true)
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(20);
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:150',
            'reference'            => 'required|string|max:50|unique:products,reference',

            'category_id'          => 'nullable|exists:categories,id',
            'category_name'        => 'nullable|string|max:150',

            'supplier_id'          => 'nullable|exists:suppliers,id',
            'supplier_name'        => 'nullable|string|max:150',
            'supplier_phone'       => 'nullable|string|max:50',
            'supplier_email'       => 'nullable|email|max:150',

            'unit'                 => 'required|in:piece,metre,kg,litre,boite,sachet,carton,paquet,flacon,tube,kit,lot,palette,sac',
            'price_buy'            => 'required|numeric|min:0',
            'price_sell'           => 'required|numeric|min:0',
            'price_sell_company'   => 'nullable|numeric|min:0',
            'price_sell_reseller'  => 'nullable|numeric|min:0',
            'price_sell_wholesale' => 'nullable|numeric|min:0',
            'quantity'             => 'required|integer|min:0',
            'alert_threshold'      => 'required|integer|min:0',
            'description'          => 'nullable|string',
        ], [
            'name.required'    => 'Le nom est obligatoire.',
            'reference.unique' => 'Cette référence existe déjà.',
            'price_sell.min'   => 'Le prix de vente ne peut pas être négatif.',
            'price_buy.min'    => 'Le prix d\'achat ne peut pas être négatif.',
        ]);

        if ($request->price_sell < $request->price_buy) {
            return back()->withInput()->withErrors([
                'price_sell' => 'Le prix de vente doit être supérieur ou égal au prix d\'achat.'
            ]);
        }

        /*

         Catégorie intelligente
        Si category_id est présent, on utilise la catégorie existante.
        Sinon, si category_name est rempli, on cherche une catégorie similaire.
         Si rien n'est trouvé, on crée automatiquement la catégorie.
        */
        $categoryId = $request->category_id;

        if (!$categoryId && $request->filled('category_name')) {
            $categoryName = trim($request->category_name);

            $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])
                ->first();

            if (!$category) {
                $category = Category::where('name', 'like', '%' . $categoryName . '%')->first();
            }

            if (!$category) {
                $category = Category::create([
                    'name' => $categoryName,
                ]);

                ActivityLog::record('category.create', "Catégorie créée automatiquement : {$category->name}");
            }

            $categoryId = $category->id;
        }

        /*

         Fournisseur intelligent

         Si supplier_id est présent, on utilise le fournisseur existant.
         Sinon, si supplier_name est rempli, on cherche par téléphone puis par nom.
         Si rien n'est trouvé, on crée automatiquement le fournisseur.
        */
        $supplierId = $request->supplier_id;

        if (!$supplierId && $request->filled('supplier_name')) {
            $cleanPhone = preg_replace('/\D+/', '', $request->supplier_phone ?? '');

            $supplier = null;

            if ($cleanPhone !== '') {
                $supplier = Supplier::whereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') = ?",
                    [$cleanPhone]
                )->first();
            }

            if (!$supplier) {
                $supplier = Supplier::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->supplier_name))])
                    ->first();
            }

            if (!$supplier) {
                $supplier = Supplier::where('name', 'like', '%' . trim($request->supplier_name) . '%')->first();
            }

            if (!$supplier) {
                $supplier = Supplier::create([
                    'name' => trim($request->supplier_name),
                    'phone' => $request->supplier_phone,
                    'email' => $request->supplier_email,
                    'is_active' => true,
                ]);

                ActivityLog::record('supplier.create', "Fournisseur créé automatiquement : {$supplier->name}");
            }

            $supplierId = $supplier->id;
        }

        $product = Product::create([
            'name' => $request->name,
            'reference' => $request->reference,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'unit' => $request->unit,
            'price_buy' => $request->price_buy,
            'price_sell' => $request->price_sell,
            'price_sell_company' => $request->price_sell_company,
            'price_sell_reseller' => $request->price_sell_reseller,
            'price_sell_wholesale' => $request->price_sell_wholesale,
            'quantity' => $request->quantity,
            'alert_threshold' => $request->alert_threshold,
            'description' => $request->description,
        ]);

        ActivityLog::record('product.create', "Produit créé : {$product->name}");

        return redirect()
            ->route('products.index')
            ->with('success', "Produit {$product->name} créé !")
            ->with('toast_notifications', [
                [
                    'type' => 'success',
                    'title' => 'Produit créé',
                    'message' => 'Le produit a été enregistré avec succès.',
                    'sound' => true,
                ],
            ]);
    }

    public function show(Product $product, AiService $aiService)
    {
        $product->load([
            'category',
            'supplier',
            'stockMovements' => fn ($q) => $q->latest()->limit(10),
        ]);

        $stockPrediction = $aiService->predictStockAlert($product);

        return view('products.show', compact('product', 'stockPrediction'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'                 => 'required|string|max:150',
            'reference'            => 'required|string|max:50|unique:products,reference,' . $product->id,
            'category_id'          => 'nullable|exists:categories,id',
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'unit'                 => 'required|in:piece,metre,kg,litre,boite,sachet,carton,paquet,flacon,tube,kit,lot,palette,sac',
            'price_buy'            => 'required|numeric|min:0',
            'price_sell'           => 'required|numeric|min:0',
            'price_sell_company'   => 'nullable|numeric|min:0',
            'price_sell_reseller'  => 'nullable|numeric|min:0',
            'price_sell_wholesale' => 'nullable|numeric|min:0',
            'alert_threshold'      => 'required|integer|min:0',
            'description'          => 'nullable|string',
        ]);

        if ($request->price_sell < $request->price_buy) {
            return back()->withInput()->withErrors([
                'price_sell' => 'Le prix de vente doit être supérieur ou égal au prix d\'achat.'
            ]);
        }

        $product->update($request->all());

        ActivityLog::record('product.update', "Produit modifié : {$product->name}");

        return redirect()
            ->route('products.index')
            ->with('success', "Produit mis à jour !")
            ->with('toast_notifications', [
                [
                    'type' => 'success',
                    'title' => 'Produit modifié',
                    'message' => 'Le produit a été mis à jour avec succès.',
                    'sound' => true,
                ],
            ]);
    }

    public function destroy(Product $product)
    {
        if ($product->saleDetails()->count() > 0) {
            return back()->withErrors([
                'error' => 'Impossible de supprimer — ce produit a des ventes associées.'
            ]);
        }

        $name = $product->name;

        $product->update([
            'is_active' => false,
        ]);

        ActivityLog::record('product.delete', "Produit désactivé : {$name}");

        return back()
            ->with('success', "Produit désactivé !")
            ->with('toast_notifications', [
                [
                    'type' => 'success',
                    'title' => 'Produit désactivé',
                    'message' => 'Le produit a été retiré de la liste active.',
                    'sound' => true,
                ],
            ]);
    }
}