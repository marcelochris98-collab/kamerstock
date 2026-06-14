<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\SupplierHistory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('products')
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20);

        return view('suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['products']);

        $totalPurchases = Purchase::where('supplier_id', $supplier->id)->sum('total_amount');
        $totalPaid = Purchase::where('supplier_id', $supplier->id)->sum('amount_paid');
        $totalDue = Purchase::where('supplier_id', $supplier->id)->sum('amount_due');
        $purchasesCount = Purchase::where('supplier_id', $supplier->id)->count();

        $lastPurchase = Purchase::where('supplier_id', $supplier->id)
            ->latest()
            ->first();

        $recentPurchases = Purchase::where('supplier_id', $supplier->id)
            ->latest()
            ->limit(10)
            ->get();

        $recentPayments = SupplierPayment::with('purchase')
            ->where('supplier_id', $supplier->id)
            ->latest()
            ->limit(10)
            ->get();

        $histories = SupplierHistory::where('supplier_id', $supplier->id)
            ->latest()
            ->limit(20)
            ->get();

        $supplierStatus = match (true) {
            $totalPurchases >= 5000000 || $purchasesCount >= 20 => 'stratégique',
            $totalPurchases >= 1000000 || $purchasesCount >= 10 => 'important',
            $totalPurchases > 0 || $purchasesCount >= 3 => 'régulier',
            default => 'occasionnel',
        };

        return view('suppliers.show', compact(
            'supplier',
            'totalPurchases',
            'totalPaid',
            'totalDue',
            'purchasesCount',
            'lastPurchase',
            'recentPurchases',
            'recentPayments',
            'histories',
            'supplierStatus'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Le nom du fournisseur est obligatoire.',
            'email.email' => 'L’adresse email est invalide.',
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        ActivityLog::record('supplier.create', "Fournisseur créé : {$supplier->name}");

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Fournisseur {$supplier->name} créé avec succès.");
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Le nom du fournisseur est obligatoire.',
            'email.email' => 'L’adresse email est invalide.',
        ]);

        $supplier->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'notes' => $request->notes,
        ]);

        ActivityLog::record('supplier.update', "Fournisseur modifié : {$supplier->name}");

        return back()->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists() || Purchase::where('supplier_id', $supplier->id)->exists()) {
            $supplier->update(['is_active' => false]);

            ActivityLog::record('supplier.disable', "Fournisseur désactivé : {$supplier->name}");

            return back()->with('success', 'Fournisseur désactivé avec succès.');
        }

        $name = $supplier->name;
        $supplier->delete();

        ActivityLog::record('supplier.delete', "Fournisseur supprimé : {$name}");

        return back()->with('success', 'Fournisseur supprimé avec succès.');
    }

    public function lookup(Request $request)
    {
        $phone = preg_replace('/\D+/', '', $request->phone ?? '');
        $name = trim($request->name ?? '');

        $query = Supplier::query()->where('is_active', true);

        if ($phone !== '') {
            $query->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?",
                ["%{$phone}%"]
            );
        }

        if ($phone === '' && $name !== '') {
            $query->where('name', 'like', "%{$name}%");
        }

        $suppliers = $query->limit(5)->get();

        return response()->json([
            'found' => $suppliers->isNotEmpty(),
            'suppliers' => $suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'email' => $supplier->email,
                    'total_due' => Purchase::where('supplier_id', $supplier->id)
                        ->where('amount_due', '>', 0)
                        ->sum('amount_due'),
                ];
            }),
        ]);
    }
}