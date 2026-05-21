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
            'address' => 'nullable|string',
            'debt_limit' => 'required|numeric|min:0',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(string $id)
    {
        $customer = Customer::with(['debts', 'transactions'])->findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'debt_limit' => 'numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $customer->update($validated);

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
