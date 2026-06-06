<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json(Customer::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tier' => 'nullable|string|max:100',
            'debt_limit' => 'nullable|numeric|min:0|max:999999999',
        ]);

        if (!isset($validated['debt_limit']) || is_null($validated['debt_limit'])) {
            $validated['debt_limit'] = 5000000;
        }

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(string $id)
    {
        if (!is_numeric($id)) {
            return response()->json(['message' => 'ID pelanggan tidak valid.'], 422);
        }

        $customer = Customer::with(['debts', 'transactions'])->findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tier' => 'nullable|string|max:100',
            'debt_limit' => 'sometimes|numeric|min:0|max:999999999',
            'is_active' => 'sometimes|boolean'
        ]);

        // CRITICAL: Explicitly whitelist safe fields — NEVER allow current_debt via API
        $safeFields = ['name', 'phone', 'address', 'tier', 'debt_limit', 'is_active'];
        $updateData = array_intersect_key($validated, array_flip($safeFields));

        $customer->update($updateData);

        return response()->json($customer);
    }

    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);

        // Safety check: Don't delete if there are unpaid debts
        if ($customer->current_debt > 0) {
            return response()->json(['message' => 'Pelanggan tidak dapat dihapus karena masih memiliki hutang.'], 400);
        }

        $customer->delete();
        return response()->json(['message' => 'Pelanggan berhasil dihapus.']);
    }
}
