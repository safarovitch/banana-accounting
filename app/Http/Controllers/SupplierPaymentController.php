<?php

namespace App\Http\Controllers;

use App\Models\SupplierOrder;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function store(Request $request, SupplierOrder $supplier)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'receipt' => 'nullable|image|max:5120',
        ]);

        $payment = $supplier->payments()->create([
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
        ]);

        // Recalculate paid amount from DB
        $newPaidAmount = $supplier->payments()->sum('amount');

        $supplier->update([
            'paid_amount' => $newPaidAmount,
        ]);

        if ($request->hasFile('receipt')) {
            $payment->addMediaFromRequest('receipt')->toMediaCollection('receipts');
        }

        return redirect()->back()->with('success', 'Платеж поставщику добавлен.');
    }
}
