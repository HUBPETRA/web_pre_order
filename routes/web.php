<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

// Halaman 1: Pilih Menu
Route::get('/', [OrderController::class, 'index'])->name('step1');

// Halaman 2: Isi Data Diri & Upload (Menerima data dari Halaman 1)
Route::post('/checkout', [OrderController::class, 'checkout'])->name('step2');

// Proses Simpan Order (Action dari Halaman 2)
Route::post('/order', [OrderController::class, 'store'])->name('order.store');

// Halaman 3: Sukses
Route::get('/success', [OrderController::class, 'success'])->name('success');

Route::get('/checkout', function () {
    return redirect()->route('step1');
});

use App\Http\Controllers\AdminController;

// Group Route Admin
Route::prefix('admin')->group(function () {
    
    // Halaman Login (Hanya bisa diakses tamu)
    Route::middleware('guest')->group(function () {
        Route::get('/', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    });

    // Halaman Dashboard (Hanya bisa diakses admin yang sudah login)
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        
        // Update Order
        Route::post('/order/{id}/update', [AdminController::class, 'updateOrderStatus'])->name('admin.order.update');
        
        // Produk
        Route::post('/product/store', [AdminController::class, 'storeProduct'])->name('admin.product.store');
        Route::post('/product/{id}/toggle', [AdminController::class, 'toggleProduct'])->name('admin.product.toggle');
    });
});