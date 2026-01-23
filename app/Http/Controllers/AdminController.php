<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Models
use App\Models\Batch;
use App\Models\Order;
use App\Models\Product;
use App\Models\DivisionDefault;
use App\Models\BatchQuota;
use App\Models\Fungsio;
use App\Models\OrderItem;

// Mail
use App\Mail\OrderVerifiedMail;

class AdminController extends Controller
{
    // =========================================================================
    // 1. AUTHENTICATION (LOGIN/LOGOUT)
    // =========================================================================

    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('admin/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin');
    }

    // =========================================================================
    // 2. DASHBOARD & MONITORING
    // =========================================================================

    /**
     * Dashboard Utama (Batch Aktif)
     */
    public function dashboard()
    {
        $activeBatch = Batch::where('is_active', true)->with('products')->first();

        if (!$activeBatch) {
            return view('admin.dashboard_empty');
        }

        // Ambil orders terbaru
        $orders = $activeBatch->orders()->orderBy('created_at', 'desc')->get();
        
        // Filter pending orders (Optimized: Filter di Collection karena data sudah di-load)
        $pendingOrders = $orders->where('status', 'Menunggu Verifikasi');

        return view('admin.dashboard_active', compact('activeBatch', 'orders', 'pendingOrders'));
    }

    /**
     * Dashboard Arsip (Detail Batch Lama)
     */
    public function showArchive(Request $request, $id)
    {
        // 1. Load Batch dengan Relasi
        $batch = Batch::with(['products', 'quotas.fungsio'])->findOrFail($id);

        // 2. Query Pesanan (dengan Search Filter)
        $query = Order::where('batch_id', $id)
                    ->with(['orderItems', 'fungsio'])
                    ->orderBy('created_at', 'desc');

        if ($request->has('q') && !empty($request->q)) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('customer_name', 'LIKE', "%{$keyword}%")
                  ->orWhere('customer_email', 'LIKE', "%{$keyword}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$keyword}%");
            });
        }

        $orders = $query->get();

        // [AJAX] Jika request live search, kembalikan partial view
        if ($request->ajax()) {
            return view('admin.partials.order_rows', compact('orders'))->render();
        }

        // 3. Siapkan Data Grafik
        $chartLabels = $batch->products->pluck('name')->toArray();
        $chartData   = $batch->products->pluck('pivot.sold')->toArray();

        // 4. Hitung Realisasi Kuota, Persentase & Denda
        // Total Denda
        $this->calculateQuotaRealization($batch);

        // LOGIKA PROFIT
        // 1. Pemasukan dari Penjualan (Order Lunas)
        $salesIncome = Order::where('batch_id', $batch->id)
                            ->where('status', 'Lunas')
                            ->sum('total_amount');

        // 2. Pemasukan dari Denda (Paid Fines)
        $fineIncome = 0;
        foreach($batch->quotas as $q) {
            if($q->is_fine_paid) {
                $fineIncome += $q->fine_amount;
            }
        }

        // 3. Total Pemasukan & Keuntungan Bersih
        $totalIncome = $salesIncome + $fineIncome;
        $netProfit = $totalIncome - $batch->starting_capital;

        // Update kolom income di DB agar sinkron (Opsional tapi bagus untuk cache)
        $batch->update(['income' => $totalIncome]);

        // Kirim data keuangan ke view
        $financials = [
            'modal' => $batch->starting_capital,
            'sales' => $salesIncome,
            'fines' => $fineIncome,
            'total_income' => $totalIncome,
            'profit' => $netProfit
        ];
        // Grouping Data Quota berdasarkan Divisi
        $quotasByDivision = $batch->quotas->groupBy(fn($item) => $item->fungsio->division ?? 'Lainnya');

        return view('admin.dashboard_archive', compact('batch', 'orders', 'chartLabels', 'chartData', 'quotasByDivision','financials'));
    }

    /**
     * Helper: Menghitung Realisasi & Denda
     */
    private function calculateQuotaRealization($batch)
    {
        $now = \Carbon\Carbon::now();
        $closeDate = \Carbon\Carbon::parse($batch->close_date);
        
        // 1. Hitung Multiplier Denda
        // Base: 10.000
        // Tambahan: 10.000 per minggu setelah tutup
        $weeksLate = 0;
        
        // Hanya hitung minggu terlambat jika PO sudah tutup
        if ($now->greaterThan($closeDate)) {
            // diffInWeeks menghitung selisih minggu penuh (7 hari, 14 hari, dst)
            $weeksLate = $closeDate->diffInWeeks($now);
        }

        $baseFine = 10000;
        $multiplier = $baseFine + ($weeksLate * 10000);

        foreach ($batch->quotas as $quota) {
            // Hitung Realisasi (Sama seperti sebelumnya)
            $realisasi = Order::where('batch_id', $batch->id)
                ->where('fungsio_id', $quota->fungsio_id)
                ->where('status', '!=', 'Ditolak')
                ->withSum('orderItems', 'quantity')
                ->get()
                ->sum('order_items_sum_quantity');

            $quota->achieved_qty = $realisasi ?? 0;
            $quota->deficit = max(0, $quota->target_qty - $quota->achieved_qty);

            // LOGIKA DENDA
            if ($quota->is_fine_paid) {
                $quota->fine_amount = $quota->paid_amount;
            } else {
                // Jika BELUM LUNAS, gunakan rumus dinamis (Defisit x Multiplier saat ini)
                $quota->fine_amount = $quota->deficit * $multiplier;
            }
        }
    }

    // =========================================================================
    // 3. MANAJEMEN BATCH (CREATE, MANAGE, CLOSE)
    // =========================================================================

    public function createBatch()
    {
        if (Batch::where('is_active', true)->exists()) {
            return redirect()->route('admin.dashboard')->with('error', 'Harap tutup PO aktif sebelum membuat baru.');
        }
        return view('admin.create_batch');
    }

    public function storeBatch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|numeric',
            'bank_account_name' => 'required|string',
            'whatsapp_link' => 'required|url',
            'close_date' => 'required|date',
            'pickup_date' => 'nullable|date|after_or_equal:close_date',
            'banner_image' => 'nullable|image|max:2048',
            'pickup_location' => 'required|string',
            'starting_capital' => 'required|numeric|min:0',
        ]);

        // Upload Gambar Banner (Jika ada)
        $imageName = null;
        if ($request->hasFile('banner_image')) {
            $imageName = time() . '_banner.' . $request->banner_image->extension();
            // Simpan di public/uploads/banners
            $request->banner_image->move(public_path('uploads/banners'), $imageName);
        }
        
        // Create Batch
        $batch = Batch::create([
            'name' => $request->name,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'whatsapp_link' => $request->whatsapp_link,
            'banner_image' => $imageName,
            'close_date' => $request->close_date,
            'pickup_date' => $request->pickup_date, 
            'is_active' => true,
            'is_reminder_sent' => false,
            'pickup_location' => $request->pickup_location,
            'starting_capital' => $request->starting_capital,
        ]);

        // Generate Default Quota
        $activeFungsios = Fungsio::where('is_active', true)->get();
        $defaults = DivisionDefault::pluck('default_quota', 'division_name')->toArray(); 

        foreach ($activeFungsios as $f) {
            $target = $defaults[$f->division] ?? 0;
            BatchQuota::create([
                'batch_id' => $batch->id,
                'fungsio_id' => $f->id,
                'target_qty' => $target
            ]);
        }

        return redirect()->route('admin.batch.menu', $batch->id)->with('success', 'Batch dibuat.');
    }

    public function closeBatch($id)
    {
        $batch = Batch::findOrFail($id);
        $batch->update(['is_active' => false]);
        return redirect()->route('admin.analytics')->with('success', 'Batch PO Berhasil Ditutup.');
    }

    // =========================================================================
    // 4. MANAJEMEN MENU / PRODUK
    // =========================================================================

    public function manageMenu($id)
    {
        $batch = Batch::with('products')->findOrFail($id);
        return view('admin.manage_menu', compact('batch'));
    }

    public function addProductToBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        // Upload Gambar
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('uploads/products'), $imageName); 

        // Buat Produk Master
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imageName, 
            'is_active' => true
        ]);

        // Attach ke Pivot Batch
        $batch = Batch::findOrFail($request->batch_id);
        $batch->products()->attach($product->id, [
            'price' => $request->price,
            'stock' => $request->stock,
            'sold' => 0,
            'is_active' => true
        ]);

        return redirect()->route('admin.batch.menu', $request->batch_id)->with('success', 'Menu berhasil ditambahkan.');
    }

    public function updateProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $dataToUpdate = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
                @unlink(public_path('uploads/products/' . $product->image));
            }
            // Upload baru
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $dataToUpdate['image'] = $imageName;
        }

        $product->update($dataToUpdate);

        // Update Pivot
        if($request->has('batch_id')) {
            $batch = Batch::findOrFail($request->batch_id);
            $batch->products()->updateExistingPivot($product->id, [
                'price' => $request->price,
                'stock' => $request->stock
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    public function toggleProduct($id)
    {
        $activeBatch = Batch::where('is_active', true)->first();
        if($activeBatch) {
            $pivot = $activeBatch->products()->where('product_id', $id)->first();
            if($pivot) {
                $newStatus = !$pivot->pivot->is_active;
                $activeBatch->products()->updateExistingPivot($id, ['is_active' => $newStatus]);
                return redirect()->back()->with('success', 'Status produk diubah.');
            }
        }
        return redirect()->back();
    }

    public function publishBatch($id)
    {
        $batch = Batch::with('products')->findOrFail($id);

        if ($batch->products->isEmpty()) {
            return redirect()->back()->with('error', 'Minimal harus ada 1 produk sebelum PO dibuka!');
        }

        $batch->update(['is_active' => true]);
        return redirect()->route('admin.dashboard')->with('success', 'Pre-Order Resmi Dibuka!');
    }

    // =========================================================================
    // 5. OPERASIONAL ORDER & INFO BATCH (UPDATE)
    // =========================================================================

    public function updateBatchInfo(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'pickup_location' => 'required|string',
            'whatsapp_link' => 'required|url',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required',
            'bank_account_name' => 'required|string',
            'pickup_date' => 'nullable|date',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        $oldLink = $batch->whatsapp_link;

        $dataToUpdate = [
            'pickup_location' => $request->pickup_location,
            'whatsapp_link' => $request->whatsapp_link,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'pickup_date' => $request->pickup_date,
        ];

        // Logika Upload Banner Image
        if ($request->hasFile('banner_image')) {
            // Hapus gambar lama (Cek kolom banner_image)
            if ($batch->banner_image && file_exists(public_path('uploads/banners/' . $batch->banner_image))) {
                @unlink(public_path('uploads/banners/' . $batch->banner_image));
            }
            
            // Upload baru
            $file = $request->file('banner_image');
            $imageName = time() . '_banner.' . $file->extension();
            $file->move(public_path('uploads/banners'), $imageName);
            
            // Masukkan ke array update
            $dataToUpdate['banner_image'] = $imageName;
        }

        $batch->update($dataToUpdate);

        // ... (Kode notifikasi email WA tetap sama) ...
        if ($oldLink !== $request->whatsapp_link) {
            $orders = Order::where('batch_id', $batch->id)
                        ->where('status', '!=', 'Ditolak')
                        ->whereNotNull('customer_email')
                        ->get();
            $countSent = 0;
            foreach ($orders as $order) {
                try {
                    $message  = "Halo {$order->customer_name},\n\n";
                    $message .= "PEMBERITAHUAN PENTING:\nLink Grup WhatsApp untuk kegiatan PO {$batch->name} telah berubah.\n\n";
                    $message .= "Link Baru: " . $request->whatsapp_link . "\n\n";
                    $message .= "Terima kasih,\nAdmin.";
                    Mail::raw($message, function ($msg) use ($order, $batch) {
                        $msg->to($order->customer_email)->subject("📢 UPDATE: Link Grup WhatsApp Baru ({$batch->name})");
                    });
                    $countSent++;
                } catch (\Exception $e) {
                    Log::error("Gagal kirim update WA: " . $e->getMessage());
                }
            }
            if ($countSent > 0) {
                return redirect()->back()->with('success', "Informasi diperbarui & Email notifikasi dikirim ke {$countSent} pemesan.");
            }
        }

        return redirect()->back()->with('success', 'Informasi PO berhasil diperbarui.');
    }

    public function updateOrderStatus(Request $request, $id)
    {
        // Load order beserta item dan batch (penting untuk email)
        $order = Order::with(['orderItems', 'batch'])->findOrFail($id);
        
        $previousStatus = $order->status; 

        // 1. Logika Pengembalian Stok (Jika status berubah jadi Ditolak)
        if ($request->status == 'Ditolak' && $previousStatus != 'Ditolak') {
            $batch = Batch::findOrFail($order->batch_id); // Ambil batch terkait
            
            foreach ($order->orderItems as $item) {
                // Cari data pivot di batch ini
                $pivotRow = $batch->products()->where('product_id', $item->product_id)->first();
                
                if($pivotRow) {
                    // Kembalikan stok (kurangi 'sold')
                    $batch->products()->updateExistingPivot($item->product_id, [
                        'sold' => max(0, $pivotRow->pivot->sold - $item->quantity) 
                    ]);
                }
            }
        }

        // 2. Lakukan Update Status di Database
        $order->update(['status' => $request->status]);

        // Kita bandingkan status yang baru ($request->status) dengan yang lama ($previousStatus)
        if ($request->status == 'Lunas' && $previousStatus != 'Lunas') {
            
            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new OrderVerifiedMail($order));
                } catch (\Exception $e) {
                    Log::error("Gagal kirim email verifikasi ke {$order->customer_email}: " . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Status pesanan diperbarui & notifikasi dikirim.');
    }

    public function toggleOrderReceived($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['is_received' => !$order->is_received]);
        $statusMsg = $order->is_received ? 'sudah diambil.' : 'belum diambil.';
        return redirect()->back()->with('success', 'Status pesanan: Barang ' . $statusMsg);
    }

    // =========================================================================
    // 6. MANAJEMEN FUNGSIO & KUOTA
    // =========================================================================

    public function manageFungsios()
    {
        $fungsios = Fungsio::orderBy('created_at', 'desc')->get();
        $defaults = DivisionDefault::all();
        return view('admin.fungsios_index', compact('fungsios', 'defaults'));
    }

    public function storeFungsio(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:fungsios,email',
            'division' => 'required|string|max:50',
        ]);

        Fungsio::create([
            'name' => $request->name,
            'email' => $request->email,
            'division' => $request->division,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Fungsio berhasil ditambahkan.');
    }

    // Update data Fungsio (Anggota)
    public function fungsios_update(Request $request, $id)
    {
        // 1. Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255', // Boleh ditambahkan rule unique jika perlu
            'division' => 'required|string',
        ]);

        // 2. Cari data berdasarkan ID
        $fungsio = \App\Models\Fungsio::findOrFail($id);

        // 3. Update data
        $fungsio->update([
            'name' => $request->name,
            'email' => $request->email,
            'division' => $request->division,
        ]);

        // 4. Kembali dengan pesan sukses
        return redirect()->back()->with('success', 'Data fungsio berhasil diperbarui.');
    }

    public function toggleFungsio($id)
    {
        $fungsio = Fungsio::findOrFail($id);
        $fungsio->is_active = !$fungsio->is_active;
        $fungsio->save();
        $status = $fungsio->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Fungsio berhasil $status.");
    }

    public function toggleFinePaid($id)
    {
        $quota = BatchQuota::findOrFail($id);
        
        if (!$quota->is_fine_paid) {
            // PROSES BAYAR (Belum Lunas -> Lunas)
            
            // Kita harus hitung denda SAAT INI dulu
            $batch = Batch::findOrFail($quota->batch_id);
            
            // Copy logika perhitungan multiplier singkat
            $now = \Carbon\Carbon::now();
            $closeDate = \Carbon\Carbon::parse($batch->close_date);
            $weeksLate = $now->greaterThan($closeDate) ? $closeDate->diffInWeeks($now) : 0;
            $multiplier = 10000 + ($weeksLate * 10000);
            
            // Hitung defisit real-time
            $realisasi = Order::where('batch_id', $batch->id)
                ->where('fungsio_id', $quota->fungsio_id)
                ->where('status', '!=', 'Ditolak')
                ->withSum('orderItems', 'quantity')
                ->get()
                ->sum('order_items_sum_quantity');
                
            $deficit = max(0, $quota->target_qty - $realisasi);
            $currentFine = $deficit * $multiplier;

            // Simpan status Lunas DAN Nominalnya
            $quota->update([
                'is_fine_paid' => true,
                'paid_amount' => $currentFine
            ]);
            
            $msg = 'Status denda: LUNAS (Rp ' . number_format($currentFine, 0, ',', '.') . ')';

        } else {
            // PROSES BATAL BAYAR (Lunas -> Belum Lunas)
            // Reset paid_amount jadi 0
            $quota->update([
                'is_fine_paid' => false,
                'paid_amount' => 0
            ]);
            
            $msg = 'Status denda: BELUM BAYAR';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function editBatchQuotas($id)
    {
        $batch = Batch::with(['quotas.fungsio'])->findOrFail($id);
        
        // Hitung realisasi manual untuk halaman ini
        foreach($batch->quotas as $quota) {
            $totalSold = Order::where('batch_id', $id)
                ->where('fungsio_id', $quota->fungsio_id)
                ->where('status', '!=', 'Ditolak')
                ->withSum('orderItems', 'quantity')
                ->get()
                ->sum('order_items_sum_quantity');
            
            $quota->achieved_qty = $totalSold ?? 0;
            $quota->remaining_qty = $quota->target_qty - ($totalSold ?? 0);
        }
        
        $defaults = DivisionDefault::all();
        $quotasByDivision = $batch->quotas->groupBy(fn($item) => $item->fungsio->division ?? 'Lainnya');

        return view('admin.batch_quotas', compact('batch', 'defaults', 'quotasByDivision'));
    }

    public function updateBatchQuotas(Request $request, $id)
    {
        $inputs = $request->input('quotas');
        if($inputs) {
            foreach ($inputs as $fungsio_id => $target) {
                BatchQuota::updateOrCreate(
                    ['batch_id' => $id, 'fungsio_id' => $fungsio_id],
                    ['target_qty' => $target]
                );
            }
        }
        return redirect()->back()->with('success', 'Target kuota berhasil diperbarui.');
    }

    public function updateDivisionDefaults(Request $request)
    {
        $defaults = $request->input('defaults');
        if($defaults) {
            foreach ($defaults as $divName => $val) {
                DivisionDefault::updateOrCreate(
                    ['division_name' => $divName],
                    ['default_quota' => $val]
                );
            }
        }
        return redirect()->back()->with('success', 'Nilai kuota divisi berhasil disimpan.');
    }

    // =========================================================================
    // 7. ANALYTICS & HISTORY
    // =========================================================================

    public function analytics(Request $request)
    {
        // 1. PERSIAPAN DATA GRAFIK (PROFIT & TOTAL ITEM)
        // Kita gunakan 'with' untuk eager loading agar loading tidak berat
        $batchesForChart = Batch::with(['orders.orderItems', 'quotas'])
                                ->orderBy('created_at', 'asc')
                                ->get();
        
        $chartLabels = [];
        $profitData  = []; // Data Garis (Keuntungan)
        $soldData    = []; // Data Bar (Jumlah Produk Terjual)

        foreach($batchesForChart as $batch) {
            // A. Hitung Total Produk Terjual (Qty) - Hanya dari order Lunas
            $totalQty = $batch->orders
                ->where('status', 'Lunas')
                ->sum(function($order) {
                    return $order->orderItems->sum('quantity');
                });

            // B. Hitung Keuangan (Profit)
            // 1. Pemasukan Penjualan
            $salesIncome = $batch->orders->where('status', 'Lunas')->sum('total_amount');
            
            // 2. Pemasukan Denda (Hanya yang statusnya sudah dibayar/collected)
            // Menggunakan kolom 'paid_amount' yang kita buat di migrasi sebelumnya
            $fineIncome = $batch->quotas->where('is_fine_paid', true)->sum('paid_amount');
            
            // 3. Hitung Net Profit
            $totalIncome = $salesIncome + $fineIncome;
            $netProfit = $totalIncome - $batch->starting_capital;

            // Masukkan ke Array
            $chartLabels[] = $batch->name;
            $soldData[]    = $totalQty;
            $profitData[]  = $netProfit;
        }

        // 2. DATA TABEL (SEARCH & PAGINATION) - TETAP SAMA
        $query = Batch::with('products')->orderBy('created_at', 'desc');

        if ($request->has('q') && !empty($request->q)) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhereHas('products', fn($subQuery) => $subQuery->where('name', 'LIKE', "%{$keyword}%"));
            });
        }

        $batches = $query->paginate(10); 

        if ($request->ajax()) {
            return view('admin.partials.batch_table', compact('batches'))->render();
        }

        // Kirim 3 variabel array ke view (Labels, Profit, Sold)
        return view('admin.dashboard_analytics', compact('batches', 'chartLabels', 'profitData', 'soldData'));
    }
    // =========================================================================
    // 8. SECURE FILE VIEWER (BUKTI TRANSFER)
    // =========================================================================

    public function showProof($filename)
    {
        // 1. Cek apakah file ada di folder 'transfers'
        if (!Storage::exists('transfers/' . $filename)) {
            abort(404); // File tidak ditemukan
        }

        // 2. Return file secara aman
        return Storage::response('transfers/' . $filename);
    }
}