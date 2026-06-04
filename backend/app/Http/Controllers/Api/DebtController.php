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

        if ($debt->status === 'paid') {
            return response()->json(['message' => 'Hutang ini sudah lunas.'], 400);
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        if ($validated['amount'] > $debt->remaining_amount) {
            return response()->json(['message' => 'Jumlah bayar melebihi sisa hutang.'], 400);
        }

        return DB::transaction(function () use ($debt, $validated) {
            // 1. Create Payment Record
            DebtPayment::create([
                'debt_id' => $debt->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'note' => $validated['note'],
            ]);

            // 2. Update Debt remaining amount and refresh model
            $debt->decrement('remaining_amount', $validated['amount']);
            $debt->refresh();

            // 3. Update Debt status if fully paid
            if ($debt->remaining_amount <= 0) {
                $debt->status = 'paid';
                $debt->save();
            }

            // 4. Update Customer current_debt (floor at 0)
            $customer = Customer::findOrFail($debt->customer_id);
            $newDebt = max(0, $customer->current_debt - $validated['amount']);
            $customer->update(['current_debt' => $newDebt]);

            return response()->json($debt->load('payments'), 200);
        });
    }
}
