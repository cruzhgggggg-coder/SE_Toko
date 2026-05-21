<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $query = Debt::with(['customer', 'transaction']);

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('due_date', 'asc')->get());
    }

    public function show(string $id)
    {
        $debt = Debt::with(['customer', 'transaction', 'payments'])->findOrFail($id);
        return response()->json($debt);
    }

    public function pay(Request $request, string $id)
    {
        $debt = Debt::findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $debt->remaining_amount,
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($debt, $validated) {
            // 1. Create Payment Record
            DebtPayment::create([
                'debt_id' => $debt->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'],
            ]);

            // 2. Update Debt remaining amount
            $debt->decrement('remaining_amount', $validated['amount']);

            // 3. Update Debt status if fully paid
            if ($debt->remaining_amount <= 0) {
                $debt->status = 'paid';
                $debt->save();
            }

            // 4. Update Customer current_debt
            $customer = Customer::findOrFail($debt->customer_id);
            $customer->decrement('current_debt', $validated['amount']);

            return response()->json($debt->load('payments'), 200);
        });
    }
}
