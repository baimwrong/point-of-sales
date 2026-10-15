<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductImportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit')->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Redirect root to dashboard or login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // POS Terminal
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/search', [PosController::class, 'searchProduct'])->name('search');
        Route::get('/product/{id}', [PosController::class, 'getProduct'])->name('product');
        Route::post('/transaction', [PosController::class, 'processTransaction'])->name('transaction');
        Route::get('/receipt/{id}', [PosController::class, 'getReceipt'])->name('receipt');
    });

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create')
            ->middleware('role:admin,manager');
        Route::post('/', [ProductController::class, 'store'])->name('store')
            ->middleware('role:admin,manager');
        Route::get('/search', [ProductController::class, 'search'])->name('search');

        // Excel / CSV Bulk Import & Export
        Route::get('/import', [ProductImportController::class, 'show'])->name('import')
            ->middleware('role:admin,manager');
        Route::post('/import', [ProductImportController::class, 'import'])->name('import.process')
            ->middleware('role:admin,manager');
        Route::get('/import/template', [ProductImportController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/export', [ProductImportController::class, 'export'])->name('export')
            ->middleware('role:admin,manager');

        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit')
            ->middleware('role:admin,manager');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update')
            ->middleware('role:admin,manager');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy')
            ->middleware('role:admin,manager');
        Route::post('/{id}/stock', [ProductController::class, 'updateStock'])->name('updateStock')
            ->middleware('role:admin,manager');
        Route::post('/{id}/toggle', [ProductController::class, 'toggleActive'])->name('toggle')
            ->middleware('role:admin,manager');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Units
    Route::prefix('units')->name('units.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::get('/create', [UnitController::class, 'create'])->name('create');
        Route::post('/', [UnitController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UnitController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UnitController::class, 'update'])->name('update');
        Route::delete('/{id}', [UnitController::class, 'destroy'])->name('destroy');
    });

    // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{id}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/search', [CustomerController::class, 'search'])->name('search');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy')
            ->middleware('role:admin,manager');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/export', [SaleController::class, 'export'])->name('export')
            ->middleware('role:admin,manager');
        Route::get('/{id}', [SaleController::class, 'show'])->name('show');
        Route::delete('/{id}', [SaleController::class, 'destroy'])->name('destroy')
            ->middleware('role:admin');
    });

    // Purchases
    Route::prefix('purchases')->name('purchases.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{id}', [PurchaseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PurchaseController::class, 'update'])->name('update');
        Route::post('/{id}/receive', [PurchaseController::class, 'receive'])->name('receive');
        Route::delete('/{id}', [PurchaseController::class, 'destroy'])->name('destroy')
            ->middleware('role:admin');
    });

    // Returns
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::get('/create', [ReturnController::class, 'create'])->name('create')
            ->middleware('role:admin,manager');
        Route::post('/', [ReturnController::class, 'store'])->name('store')
            ->middleware('role:admin,manager');
        Route::get('/{id}', [ReturnController::class, 'show'])->name('show');
        Route::delete('/{id}', [ReturnController::class, 'destroy'])->name('destroy')
            ->middleware('role:admin');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    // Users (admin only)
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('toggleActive');
    });

    // Settings (admin only)
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/store', [SettingController::class, 'updateStore'])->name('updateStore');
        Route::post('/tax', [SettingController::class, 'updateTax'])->name('updateTax');
        Route::post('/receipt', [SettingController::class, 'updateReceipt'])->name('updateReceipt');
    });
});
