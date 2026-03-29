<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SupplierOrder;

class SupplierOrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Suppliers/Index', [
            'orders' => SupplierOrder::latest('order_date')->get()
        ]);
    }
}
