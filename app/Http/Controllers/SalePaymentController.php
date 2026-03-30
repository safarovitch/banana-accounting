<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SalePaymentController extends Controller
{
    public function store(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'receipt' => 'nullable|image|max:5120', // max 5MB
        ]);

        $payment = $sale->payments()->create([
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
        ]);

        $newPaidAmount = collect($sale->payments)->sum('amount') + $validated['amount'];
        // Wait, since we just created it, sale->payments relationship might not include it if cached, but we can just use DB query or calculate.
        // Actually, let's recalculate from DB to be purely safe.
        $newPaidAmount = $sale->payments()->sum('amount');
        
        $status = $sale->status;
        if ($newPaidAmount >= $sale->total_amount && $sale->status !== 'swap') {
            $status = 'completed';
        }

        $sale->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status
        ]);

        if ($request->hasFile('receipt')) {
            $payment->addMediaFromRequest('receipt')->toMediaCollection('receipts');
        }

        return redirect()->back()->with('success', 'Платеж успешно добавлен.');
    }
}
