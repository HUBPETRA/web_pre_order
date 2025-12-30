<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController; 

// --- HALAMAN USER (FRONTEND) ---

// Halaman 1: Pilih Menu
Route::get('/', [OrderController::class, 'index'])->name('step1');

// Halaman 2: Isi Data Diri & Upload
Route::post('/checkout', [OrderController::class, 'checkout'])->name('step2');

// Proses Simpan Order
Route::post('/order', [OrderController::class, 'store'])->name('order.store');

// Halaman 3: Sukses
Route::get('/success', [OrderController::class, 'success'])->name('success');

// Redirect jika user iseng ketik /checkout manual
Route::get('/checkout', function () {
    return redirect()->route('step1');
});


// --- HALAMAN ADMIN (BACKEND) ---

Route::prefix('admin')->group(function () {
    
    // GUEST: Hanya bisa diakses jika BELUM login
    Route::middleware('guest')->group(function () {
        Route::get('/', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    });

    // AUTH: Hanya bisa diakses jika SUDAH login
    Route::middleware('auth')->group(function () {
        
        // Rute Logout
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

        // Dashboard Aktif (Default)
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // BUAT PO BARU
        
        // Step 1: Info Batch (Nama, Bank, WA)
        Route::get('/create-batch/step1', [AdminController::class, 'createBatchStep1'])->name('admin.batch.step1');
        Route::post('/create-batch/step1', [AdminController::class, 'storeBatchStep1'])->name('admin.batch.storeStep1');

        // Step 2: Kelola Produk (Tampil List & Modal Tambah)
        Route::get('/create-batch/step2/{id}', [AdminController::class, 'createBatchStep2'])->name('admin.batch.step2');

        // Action: Tambah Produk Baru via Modal
        Route::post('/create-batch/add-product', [AdminController::class, 'addProductToBatch'])->name('admin.batch.addProduct');

        // Action: Selesai & Terbitkan (Aktifkan Batch)
        Route::post('/create-batch/publish/{id}', [AdminController::class, 'publishBatch'])->name('admin.batch.publish');

        // OPERASIONAL & ARSIP

        // Tutup PO
        Route::post('/close-batch/{id}', [AdminController::class, 'closeBatch'])->name('admin.batch.close');

        // Dashboard Arsip
        Route::get('/archive/{id}', [AdminController::class, 'showArchive'])->name('admin.archive.detail');

        // Dashboard Analitik
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
        
        // Update Order (Terima/Tolak)
        Route::post('/order/{id}/update', [AdminController::class, 'updateOrderStatus'])->name('admin.order.update');
        
        // Produk Toggle (Aktif/Nonaktif per item)
        Route::post('/product/{id}/toggle', [AdminController::class, 'toggleProduct'])->name('admin.product.toggle');
        
        // Simpan Produk Master (Opsional jika ada fitur master produk terpisah)
        Route::post('/product/store', [AdminController::class, 'storeProduct'])->name('admin.product.store');
    });
});

// SOLUSI ERROR 404 /home
Route::get('/home', function() {
    return redirect()->route('admin.dashboard');
});