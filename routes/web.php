<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. HALAMAN USER (FRONTEND - ORDER MAKANAN)
// =========================================================================

Route::controller(OrderController::class)->group(function () {
    // Halaman 1: Pilih Menu
    Route::get('/', 'index')->name('step1');

    // Halaman 2: Isi Data Diri & Upload (Checkout)
    Route::post('/checkout', 'checkout')->name('step2');

    // Proses Simpan Order ke Database
    Route::post('/order', 'store')->name('order.store');

    // Halaman 3: Sukses
    Route::get('/success', 'success')->name('success');
});

// Redirect user iseng ke halaman utama
Route::get('/checkout', function () {
    return redirect()->route('step1');
});


// =========================================================================
// 2. HALAMAN ADMIN (BACKEND)
// =========================================================================

Route::prefix('admin')->group(function () {
    
    // --- GUEST: Hanya bisa diakses jika BELUM login ---
    Route::middleware('guest')->group(function () {
        Route::get('/', [AdminController::class, 'showLogin'])->name('login'); // Halaman Login
        Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post'); // Proses Login
    });

    // --- AUTH: Hanya bisa diakses jika SUDAH login ---
    Route::middleware('auth')->group(function () {
        
        // Logout
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

        // Dashboard Utama
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // --- MANAJEMEN BATCH (KEGIATAN PO) ---
        Route::get('/create-batch', [AdminController::class, 'createBatch'])->name('admin.batch.create');
        Route::post('/create-batch', [AdminController::class, 'storeBatch'])->name('admin.batch.store');
        Route::post('/batch/update-info', [AdminController::class, 'updateBatchInfo'])->name('admin.batch.update_info');
        Route::post('/batch/{id}/publish', [AdminController::class, 'publishBatch'])->name('admin.batch.publish');
        Route::post('/close-batch/{id}', [AdminController::class, 'closeBatch'])->name('admin.batch.close'); // Tutup PO

        // --- MANAJEMEN MENU & PRODUK ---
        Route::get('/batch/{id}/menu', [AdminController::class, 'manageMenu'])->name('admin.batch.menu');
        Route::post('/batch/add-product', [AdminController::class, 'addProductToBatch'])->name('admin.batch.add_product');
        Route::post('/product/update', [AdminController::class, 'updateProduct'])->name('admin.product.update'); // Edit Menu
        Route::post('/product/{id}/toggle', [AdminController::class, 'toggleProduct'])->name('admin.product.toggle'); // Aktif/Nonaktif Item

        // --- MANAJEMEN KUOTA ---
        Route::get('/batch/{id}/quotas', [AdminController::class, 'editBatchQuotas'])->name('admin.batch.quotas');
        Route::post('/batch/{id}/quotas', [AdminController::class, 'updateBatchQuotas'])->name('admin.batch.quotas.update');
        Route::post('/quota/{id}/toggle-fine', [AdminController::class, 'toggleFinePaid'])->name('admin.quota.toggle_fine'); // Denda Lunas

        // --- MANAJEMEN ORDER (PESANAN MASUK) ---
        Route::post('/order/{id}/update', [AdminController::class, 'updateOrderStatus'])->name('admin.order.update'); // Terima/Tolak
        Route::post('/order/{id}/toggle-received', [AdminController::class, 'toggleOrderReceived'])->name('admin.order.toggle_received'); // Sudah Diambil

        // --- MANAJEMEN ARSIP & ANALITIK ---
        Route::get('/archive/{id}', [AdminController::class, 'showArchive'])->name('admin.archive.detail');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');

        // --- MANAJEMEN FUNGSIO (ANGGOTA) ---
        Route::get('/fungsios', [AdminController::class, 'manageFungsios'])->name('admin.fungsios.index');
        Route::post('/fungsios', [AdminController::class, 'storeFungsio'])->name('admin.fungsios.store');
        Route::post('/fungsios/{id}/toggle', [AdminController::class, 'toggleFungsio'])->name('admin.fungsios.toggle');
        Route::put('/fungsios/{id}', [AdminController::class, 'fungsios_update'])->name('admin.fungsios.update');

        // --- PENGATURAN LAINNYA ---
        Route::post('/division-defaults', [AdminController::class, 'updateDivisionDefaults'])->name('admin.division.defaults');
        //Bukti Transfer
        Route::get('/proof/{filename}', [AdminController::class, 'showProof'])->name('admin.proof.show');
    });
});

// Redirect /home (bawaan Laravel) ke Dashboard Admin
Route::get('/home', function() {
    return redirect()->route('admin.dashboard');
});