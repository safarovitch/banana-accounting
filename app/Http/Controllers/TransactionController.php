<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('description', 'ilike', "%{$search}%");
            })
            ->latest('transaction_date')
            ->paginate(20)
            ->withQueryString();

        // Summary stats
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only('search', 'type'),
            'summary' => [
                'totalIncome' => (float) $totalIncome,
                'totalExpense' => (float) $totalExpense,
                'balance' => (float) ($totalIncome - $totalExpense),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Transactions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|image|max:5120',
        ]);

        $transaction = Transaction::create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
        ]);

        if ($request->hasFile('receipt')) {
            $transaction->addMediaFromRequest('receipt')->toMediaCollection('receipts');
        }

        return redirect()->route('transactions.index')->with('success', 'Запись успешно добавлена.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Запись удалена.');
    }
}
