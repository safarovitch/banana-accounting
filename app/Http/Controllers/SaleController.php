<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sale;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::query()
            ->when($request->search, function ($query, $search) {
                // ilike is used for Postgres case-insensitive search
                $query->where('client_name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $clients = Sale::select('client_name')->distinct()->pluck('client_name');
        
        return Inertia::render('Sales/Create', [
            'clients' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|in:debt,completed,swap',
            'swap_description' => 'required_if:status,swap|nullable|string',
            'sale_date' => 'required|date',
        ]);

        Sale::create($validated);

        return redirect()->route('sales.index')->with('success', 'Продажа успешно добавлена.');
    }
}
