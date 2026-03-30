<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SupplierOrder;

class SupplierOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = SupplierOrder::query()
            ->when($request->search, function ($query, $search) {
                $query->where('supplier_name', 'ilike', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest('order_date')
            ->paginate(20)
            ->withQueryString();

        // Summary
        $totalDebt = SupplierOrder::selectRaw('SUM(total_expected_cost - paid_amount) as debt')->value('debt') ?? 0;
        $totalOrders = SupplierOrder::count();
        $preorderCount = SupplierOrder::where('status', 'preorder')->count();

        return Inertia::render('Suppliers/Index', [
            'orders' => $orders,
            'filters' => $request->only('search', 'status'),
            'summary' => [
                'totalDebt' => (float) $totalDebt,
                'totalOrders' => $totalOrders,
                'preorderCount' => $preorderCount,
            ],
        ]);
    }

    public function create()
    {
        $suppliers = SupplierOrder::select('supplier_name')->distinct()->pluck('supplier_name');

        return Inertia::render('Suppliers/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_expected_cost' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|in:preorder,received',
            'order_date' => 'required|date',
            'received_date' => 'nullable|date',
        ]);

        SupplierOrder::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Заказ поставщику добавлен.');
    }

    public function show(SupplierOrder $supplier)
    {
        $supplier->load(['payments' => function ($query) {
            $query->latest('payment_date');
        }, 'payments.media']);

        // Append receipt URLs
        $supplier->payments->each(function ($payment) {
            $payment->receipt_url = $payment->getFirstMediaUrl('receipts');
        });

        return Inertia::render('Suppliers/Show', [
            'order' => $supplier,
        ]);
    }

    public function update(Request $request, SupplierOrder $supplier)
    {
        $validated = $request->validate([
            'status' => 'required|in:preorder,received',
            'received_date' => 'nullable|date',
        ]);

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Статус обновлен.');
    }
}
