<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('products')
                             ->orderBy('name')
                             ->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Le nom est obligatoire.',
        ]);

        $supplier = Supplier::create($request->all());

        ActivityLog::record('supplier.create', "Fournisseur créé : {$supplier->name}");

        return back()->with('success', " Fournisseur {$supplier->name} créé !");
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'  => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        $supplier->update($request->all());

        ActivityLog::record('supplier.update', "Fournisseur modifié : {$supplier->name}");

        return back()->with('success', " Fournisseur mis à jour !");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->count() > 0) {
            return back()->withErrors([
                'error' => ' Impossible de supprimer — ce fournisseur a des produits associés.'
            ]);
        }

        $supplier->update(['is_active' => false]);
        ActivityLog::record('supplier.delete', "Fournisseur désactivé : {$supplier->name}");

        return back()->with('success', " Fournisseur désactivé !");
    }
}