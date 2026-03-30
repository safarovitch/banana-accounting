<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return redirect('/dashboard');
});

Route::get('/dashboard', function () {
  $totalSales = \App\Models\Sale::sum('total_amount');
  $totalDebt = \App\Models\Sale::where('status', 'debt')->selectRaw('SUM(total_amount - paid_amount) as debt')->value('debt') ?? 0;
  $totalIncome = \App\Models\Transaction::where('type', 'income')->sum('amount');
  $totalExpense = \App\Models\Transaction::where('type', 'expense')->sum('amount');
  $supplierDebt = \App\Models\SupplierOrder::selectRaw('SUM(total_expected_cost - paid_amount) as debt')->value('debt') ?? 0;
  $recentSales = \App\Models\Sale::latest('sale_date')->take(5)->get();
  $recentTransactions = \App\Models\Transaction::latest('transaction_date')->take(5)->get();

  return Inertia::render('Dashboard', [
    'stats' => [
      'totalSales' => (float) $totalSales,
      'totalDebt' => (float) $totalDebt,
      'totalIncome' => (float) $totalIncome,
      'totalExpense' => (float) $totalExpense,
      'supplierDebt' => (float) $supplierDebt,
      'balance' => (float) ($totalIncome - $totalExpense),
    ],
    'recentSales' => $recentSales,
    'recentTransactions' => $recentTransactions,
  ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::resource('sales', \App\Http\Controllers\SaleController::class);
  Route::post('sales/{sale}/payments', [\App\Http\Controllers\SalePaymentController::class, 'store'])->name('sale-payments.store');

  Route::resource('transactions', \App\Http\Controllers\TransactionController::class);

  Route::resource('suppliers', \App\Http\Controllers\SupplierOrderController::class);
  Route::post('suppliers/{supplier}/payments', [\App\Http\Controllers\SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
});

require __DIR__ . '/auth.php';
