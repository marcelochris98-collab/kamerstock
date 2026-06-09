<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
       $suppliers = Supplier::where('is_active', true)
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
    $name = $supplier->name;

    $supplier->update([
        'is_active' => false,
    ]);

    ActivityLog::record('supplier.delete', "Fournisseur désactivé : {$name}");

    return back()
        ->with('success', "Fournisseur désactivé !")
        ->with('toast_notifications', [
            [
                'type' => 'success',
                'title' => 'Fournisseur désactivé',
                'message' => 'Le fournisseur a été retiré de la liste active.',
                'sound' => true,
            ],
        ]);
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
            ];
        }),
    ]);
}
}
