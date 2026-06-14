<?php

use App\Http\Controllers\Auth\BranchSelectionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest (unauthenticated) routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes that complete the login flow (no post-login gate)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

    Route::get('/select-branch', [BranchSelectionController::class, 'show'])->name('branch.select');
    Route::post('/select-branch', [BranchSelectionController::class, 'select'])->name('branch.select.store');
});

/*
|--------------------------------------------------------------------------
| Application routes (require OTP verification + branch selection)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'post.login', 'module.permission'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Point of Sale
    Route::view('/pos', 'pos.index')->name('pos.index');

    // POS Shifts
    Route::get('/shifts', [\App\Http\Controllers\ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/open', [\App\Http\Controllers\ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [\App\Http\Controllers\ShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/{shift}/close', [\App\Http\Controllers\ShiftController::class, 'close'])->name('shifts.close');
    Route::post('/shifts/{shift}/close', [\App\Http\Controllers\ShiftController::class, 'storeClose'])->name('shifts.close.store');
    Route::post('/shifts/{shift}/approve', [\App\Http\Controllers\ShiftController::class, 'approve'])->name('shifts.approve');
    Route::post('/shifts/{shift}/reject', [\App\Http\Controllers\ShiftController::class, 'reject'])->name('shifts.reject');

    // Inventory
    Route::view('/inventory', 'inventory.index')->name('inventory.index');

    // Stock Adjustments
    Route::get('/stock-adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
    Route::view('/stock-adjustments/create', 'stock-adjustments.create')->name('stock-adjustments.create');
    Route::post('/stock-adjustments/{stockAdjustment}/approve', [\App\Http\Controllers\StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
    Route::post('/stock-adjustments/{stockAdjustment}/reject', [\App\Http\Controllers\StockAdjustmentController::class, 'reject'])->name('stock-adjustments.reject');

    // Expiry & Low Stock
    Route::view('/expiry', 'inventory.expiry')->name('expiry.index');
    Route::get('/low-stock', [\App\Http\Controllers\LowStockController::class, 'index'])->name('low-stock.index');

    // Stock Transfers
    Route::get('/stock-transfers', [\App\Http\Controllers\StockTransferController::class, 'index'])->name('stock-transfers.index');
    Route::view('/stock-transfers/create', 'stock-transfers.create')->name('stock-transfers.create');
    Route::post('/stock-transfers/{stockTransfer}/dispatch', [\App\Http\Controllers\StockTransferController::class, 'dispatchTransfer'])->name('stock-transfers.dispatch');
    Route::post('/stock-transfers/{stockTransfer}/receive', [\App\Http\Controllers\StockTransferController::class, 'receive'])->name('stock-transfers.receive');

    // Medicines
    Route::resource('medicines', \App\Http\Controllers\MedicineController::class);

    // Customers & Suppliers (master data)
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::resource('branches', \App\Http\Controllers\BranchController::class);

    // Sales
    Route::get('/sales', [\App\Http\Controllers\SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [\App\Http\Controllers\SaleController::class, 'show'])->name('sales.show');

    // Sale Returns
    Route::get('/sale-returns', [\App\Http\Controllers\SaleReturnController::class, 'index'])->name('sale-returns.index');
    Route::view('/sale-returns/create', 'sale-returns.create')->name('sale-returns.create');

    // Purchase Returns
    Route::get('/purchase-returns', [\App\Http\Controllers\PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
    Route::view('/purchase-returns/create', 'purchase-returns.create')->name('purchase-returns.create');

    // Ledger
    Route::view('/ledger', 'ledger.index')->name('ledger.index');

    // Reports
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');

    // Expenses
    Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [\App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::post('/expenses/{expense}/approve', [\App\Http\Controllers\ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::post('/expenses/{expense}/reject', [\App\Http\Controllers\ExpenseController::class, 'reject'])->name('expenses.reject');

    // Purchases
    Route::get('/purchases', [\App\Http\Controllers\PurchaseController::class, 'index'])->name('purchases.index');
    Route::view('/purchases/create', 'purchases.create')->name('purchases.create');
    Route::get('/purchases/{purchase}', [\App\Http\Controllers\PurchaseController::class, 'show'])->name('purchases.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // User management
    Route::post('/users/{user}/password', [UserController::class, 'sendPasswordReset'])->name('users.password');
    Route::patch('/users/{user}/block', [UserController::class, 'toggleBlock'])->name('users.block');
    Route::resource('users', UserController::class)->except('show');

    // Roles & Permissions
    Route::get('/roles', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('/roles/{role}/edit', [\App\Http\Controllers\RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [\App\Http\Controllers\RolePermissionController::class, 'update'])->name('roles.update');
    Route::post('/roles', [\App\Http\Controllers\RolePermissionController::class, 'store'])->name('roles.store');
    Route::delete('/roles/{role}', [\App\Http\Controllers\RolePermissionController::class, 'destroy'])->name('roles.destroy');
});
