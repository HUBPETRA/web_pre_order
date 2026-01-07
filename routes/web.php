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
        Route::get('/create-batch', [AdminController::class, 'createBatch'])->name('admin.batch.create');
        // 1. Create Batch (Hanya Input Nama & Bank) -> Redirect ke Manage Menu
        Route::post('/create-batch', [AdminController::class, 'storeBatch'])->name('admin.batch.store');

        // 2. Halaman Kelola Menu (Bekas Step 2)
        Route::get('/batch/{id}/menu', [AdminController::class, 'manageMenu'])->name('admin.batch.menu');

        // 3. Proses Tambah Produk (Action)
        Route::post('/batch/add-product', [AdminController::class, 'addProductToBatch'])->name('admin.batch.add_product');

        // 4. Proses Publish (Opsional, jika ingin tombol Aktifkan PO di halaman menu)
        Route::post('/batch/{id}/publish', [AdminController::class, 'publishBatch'])->name('admin.batch.publish');

        // OPERASIONAL & ARSIP
        // Route update produk (Edit Menu)
        Route::post('/product/update', [AdminController::class, 'updateProduct'])->name('admin.product.update');

        // Route update Info Batch (Edit WA & Bank di Dashboard)
        Route::post('/batch/update-info', [AdminController::class, 'updateBatchInfo'])->name('admin.batch.update_info');

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

        // --- MANAJEMEN FUNGSIO (PENGURUS) ---
        Route::get('/fungsios', [AdminController::class, 'manageFungsios'])->name('admin.fungsios.index'); // Halaman List & Input
        Route::post('/fungsios', [AdminController::class, 'storeFungsio'])->name('admin.fungsios.store');   // Proses Simpan
        Route::post('/fungsios/{id}/toggle', [AdminController::class, 'toggleFungsio'])->name('admin.fungsios.toggle'); // Aktif/Nonaktif

        // --- SETTING KUOTA ---
        // Halaman Kelola Kuota Batch Tertentu
        Route::get('/batch/{id}/quotas', [AdminController::class, 'editBatchQuotas'])->name('admin.batch.quotas');
        Route::post('/batch/{id}/quotas', [AdminController::class, 'updateBatchQuotas'])->name('admin.batch.quotas.update');

        // Setting Default Divisi (Global)
        Route::post('/division-defaults', [AdminController::class, 'updateDivisionDefaults'])->name('admin.division.defaults');

        //Mail
        // Halaman Kelola Template Email (Baru)
        Route::get('/batch/{id}/mail', [App\Http\Controllers\AdminController::class, 'manageMail'])->name('admin.batch.mail');
        Route::post('/batch/update-mail-template', [AdminController::class, 'updateMailTemplate'])->name('admin.batch.update_mail');
    });
});

// SOLUSI ERROR 404 /home
Route::get('/home', function() {
    return redirect()->route('admin.dashboard');
});