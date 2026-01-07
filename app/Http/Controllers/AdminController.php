<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Batch;
use App\Models\Order;
use App\Models\Product;
use App\Models\DivisionDefault;
use App\Models\BatchQuota;
use App\Models\Fungsio;
use App\Models\OrderItem; // Pastikan model OrderItem di-import
use Illuminate\Support\Str; // Import Str

class AdminController extends Controller
{
    // 1. AUTHENTICATION (LOGIN/LOGOUT)
    
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
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin');
    }

    // 2. DASHBOARD & MONITORING

    // Dashboard Utama (Batch Aktif)
    public function dashboard()
    {
        // Cari batch yang sedang aktif
        $activeBatch = Batch::where('is_active', true)->with('products')->first();

        // Jika tidak ada batch aktif, tampilkan halaman kosong
        if (!$activeBatch) {
            return view('admin.dashboard_empty'); 
        }

        // Statistik Penjualan Batch Ini
        $orders = $activeBatch->orders()->orderBy('created_at', 'desc')->get();
        
        // Ambil order yang statusnya "Menunggu Verifikasi"
        $pendingOrders = $orders->where('status', 'Menunggu Verifikasi');

        return view('admin.dashboard_active', compact('activeBatch', 'orders', 'pendingOrders'));
    }

    // Dashboard Arsip
    public function showArchive($id)
    {
        // 1. Load Batch beserta Produk & Data Kuota+Fungsio
        $batch = \App\Models\Batch::with(['products', 'quotas.fungsio'])->findOrFail($id);

        // 2. Ambil List Pesanan
        $orders = \App\Models\Order::where('batch_id', $id)
                    ->with('orderItems')
                    ->orderBy('created_at', 'desc')
                    ->get();

        // 3. Siapkan Data Grafik Produk (Existing)
        $chartLabels = [];
        $chartData = [];
        foreach($batch->products as $product) {
            $chartLabels[] = $product->name;
            $chartData[] = $product->pivot->sold; 
        }

        // 4. [BARU] Hitung Rapor Kinerja Fungsio di Batch Ini
        // Kita hitung ulang berdasarkan order yang masuk di batch ini
        foreach($batch->quotas as $quota) {
            $realisasi = \App\Models\Order::where('batch_id', $id)
                ->where('fungsio_id', $quota->fungsio_id)
                ->where('status', '!=', 'Ditolak') // Pesanan ditolak tidak dihitung
                ->withSum('orderItems', 'quantity') // Hitung total porsi (bukan orang)
                ->get()
                ->sum('order_items_sum_quantity');
            
            // Simpan data kalkulasi ke objek quota (temporary untuk view)
            $quota->achieved_qty = $realisasi ?? 0;
            
            // Hitung persentase keberhasilan
            $quota->percentage = $quota->target_qty > 0 
                ? ($quota->achieved_qty / $quota->target_qty) * 100 
                : 0;
        }

        // Grouping berdasarkan divisi agar tampilan rapi
        $quotasByDivision = $batch->quotas->groupBy(function($item) {
            return $item->fungsio->division ?? 'Lainnya';
        });

        return view('admin.dashboard_archive', compact('batch', 'orders', 'chartLabels', 'chartData', 'quotasByDivision'));
    }

    // PEMBUATAN BATCH BARU


    // STEP 1: Halaman Input Info Dasar
    public function createBatch()
    {
        if (Batch::where('is_active', true)->exists()) {
            return redirect()->route('admin.dashboard')->with('error', 'Harap tutup PO aktif sebelum membuat baru.');
        }
        return view('admin.create_batch');
    }

    // Simpan Draft Batch
    public function storeBatch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|numeric',
            'bank_account_name' => 'required|string',
            'whatsapp_link' => 'required|url',
            'close_date' => 'date'
        ]);

        // Buat Batch 
        // Template Default (Updated: Pakai {detail_pesanan})
        $defaultTemplate = "Halo {nama_pemesan},\n\nTerima kasih sudah memesan di {nama_kegiatan}.\nBerikut adalah rincian pesanan Anda:\n\n{detail_pesanan}\n\nPO akan ditutup besok. Pastikan pembayaran sudah lunas.\n\nSalam,\nAdmin";

        $batch = Batch::create([
            'name' => $request->name,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'whatsapp_link' => $request->whatsapp_link,
            'close_date' => $request->close_date,
            'is_active' => true,
            'mail_message' => $defaultTemplate, // <--- ISI DEFAULT BARU
            'is_reminder_sent' => false
        ]);

        // 3. Generate Kuota Otomatis (Fitur sebelumnya)
        $activeFungsios = \App\Models\Fungsio::where('is_active', true)->get();
        $defaults = \App\Models\DivisionDefault::pluck('default_quota', 'division_name')->toArray(); 

        foreach ($activeFungsios as $f) {
            $target = $defaults[$f->division] ?? 0;
            \App\Models\BatchQuota::create([
                'batch_id' => $batch->id,
                'fungsio_id' => $f->id,
                'target_qty' => $target
            ]);
        }
        return redirect()->route('admin.batch.menu', $batch->id)->with('success', 'Batch dibuat. Template email default telah dipasang.');
    }

    public function manageMail($id)
    {
        $batch = \App\Models\Batch::findOrFail($id);
        return view('admin.manage_mail', compact('batch'));
    }

    // [UPDATE] Update Template (Redirectnya ubah agar kembali ke halaman mail, bukan dashboard)
    public function updateMailTemplate(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
            'mail_message' => 'required|string',
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        $batch->update(['mail_message' => $request->mail_message]);

        // Redirect back akan otomatis kembali ke halaman manage_mail
        return redirect()->back()->with('success', 'Template pesan email berhasil diperbarui.');
    }

    // STEP 2: Halaman Kelola Produk
    public function manageMenu($id)
    {
        $batch = \App\Models\Batch::with('products')->findOrFail($id);
        return view('admin.manage_menu', compact('batch'));
    }

    // Tambah Produk via Modal
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
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
        }

        // Buat Produk 
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imageName, 
            'is_active' => true
        ]);

        // Sambungkan ke Batch 
        $batch = Batch::findOrFail($request->batch_id);
        $batch->products()->attach($product->id, [
            'price' => $request->price,
            'stock' => $request->stock,
            'sold' => 0,
            'is_active' => true
        ]);

        return redirect()->route('admin.batch.menu', $request->batch_id)->with('success', 'Menu berhasil ditambahkan.');
    }

    // FINAL STEP: Terbitkan (Aktifkan) PO
    public function publishBatch($id)
    {
        $batch = Batch::with('products')->findOrFail($id);

        if ($batch->products->isEmpty()) {
            return redirect()->back()->with('error', 'Minimal harus ada 1 produk sebelum PO dibuka!');
        }

        // Aktifkan Batch
        $batch->update(['is_active' => true]);

        return redirect()->route('admin.dashboard')->with('success', 'Pre-Order Resmi Dibuka!');
    }

    // OPERASIONAL (UPDATE STATUS, TUTUP PO, DLL)

    // FUNGSI 1: Update Produk (Menu)
    public function updateProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|image|max:2048', // Nullable: tidak wajib upload ulang
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // 1. Update Tabel Produk Master
        $dataToUpdate = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        // Cek jika ada upload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada (opsional, biar hemat storage)
            if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
                unlink(public_path('uploads/products/' . $product->image));
            }
            // Upload baru
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $dataToUpdate['image'] = $imageName;
        }

        $product->update($dataToUpdate);

        // 2. Update Tabel Pivot (Harga & Stok di Batch ini)
        // Kita perlu tahu batch_id nya. Ambil dari input hidden atau cari batch aktif.
        // Asumsi: Kita edit produk dalam konteks batch yang sedang dibuka.
        if($request->has('batch_id')) {
            $batch = Batch::findOrFail($request->batch_id);
            $batch->products()->updateExistingPivot($product->id, [
                'price' => $request->price,
                'stock' => $request->stock
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    // FUNGSI 2: Update Info Batch (Dashboard)
    public function updateBatchInfo(Request $request)
    {
        $request->validate([
            'batch_id' => 'required',
            'whatsapp_link' => 'required|url',
            'bank_name' => 'required',
            'bank_account_number' => 'required',
            'bank_account_name' => 'required',
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        $batch->update([
            'whatsapp_link' => $request->whatsapp_link,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ]);

        return redirect()->back()->with('success', 'Informasi PO berhasil diupdate.');
    }

    // Terima/Tolak Pesanan
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        
        if ($request->status == 'Ditolak' && $order->status != 'Ditolak') {
            
            $batch = Batch::findOrFail($order->batch_id);

            foreach ($order->orderItems as $item) {
                // Cari produk ini di tabel pivot batch
                $pivotRow = $batch->products()->where('product_id', $item->product_id)->first();
                
                if($pivotRow) {
                    $currentSold = $pivotRow->pivot->sold;
                    
                    // Update pivot: Kurangi angka 'sold'
                    // max(0, ...) untuk menjaga agar tidak minus
                    $batch->products()->updateExistingPivot($item->product_id, [
                        'sold' => max(0, $currentSold - $item->quantity) 
                    ]);
                }
            }
        }

        // Update status pesanan
        $order->update(['status' => $request->status]);
        
        return redirect()->back()->with('success', 'Status pesanan diperbarui.');
    }

    // Matikan/Hidupkan Produk di Batch (Jika stok habis)
    public function toggleProduct($id)
    {
        
        $activeBatch = Batch::where('is_active', true)->first();
        if($activeBatch) {
            $pivot = $activeBatch->products()->where('product_id', $id)->first();
            if($pivot) {
                // Toggle status is_active di tabel pivot
                $newStatus = !$pivot->pivot->is_active;
                $activeBatch->products()->updateExistingPivot($id, ['is_active' => $newStatus]);
                return redirect()->back()->with('success', 'Status produk diubah.');
            }
        }
        
        return redirect()->back();
    }
    // Halaman Grafik & List History (Halaman Utama Arsip)
    public function analytics(Request $request)
    {
        // 1. Data untuk Grafik (Semua Batch)
        $batchesForChart = \App\Models\Batch::orderBy('created_at', 'asc')->get();
        $chartLabels = [];
        $chartData = [];

        foreach ($batchesForChart as $batch) {
            $lunasCount = $batch->orders()->where('status', 'Lunas')->count();
            $chartLabels[] = $batch->name;
            $chartData[] = $lunasCount;
        }

        // 2. Data untuk Tabel List
        $query = \App\Models\Batch::with('products')->orderBy('created_at', 'desc');

        // [PERBAIKAN] Logika Pencarian: Nama Batch ATAU Nama Produk
        if ($request->has('q') && !empty($request->q)) {
            $keyword = $request->q;
            
            $query->where(function($q) use ($keyword) {
                // Cari berdasarkan Nama Kegiatan PO
                $q->where('name', 'LIKE', "%{$keyword}%")
                  // ATAU Cari berdasarkan Nama Produk di dalamnya
                  ->orWhereHas('products', function($subQuery) use ($keyword) {
                      $subQuery->where('name', 'LIKE', "%{$keyword}%");
                  });
            });
        }

        $batches = $query->paginate(10); 

        // Untuk Search AJAX (Live Search)
        if ($request->ajax()) {
            // Pastikan file 'admin.partials.batch_table' ada.
            // Jika tidak ada/error, hapus blok if ini.
            return view('admin.partials.batch_table', compact('batches'))->render();
        }

        return view('admin.dashboard_analytics', compact('batches', 'chartLabels', 'chartData'));
    }

    // Tutup Batch 
    public function closeBatch($id)
    {
        $batch = Batch::findOrFail($id);
        $batch->update(['is_active' => false]);
        return redirect()->route('admin.analytics')->with('success', 'Batch PO Berhasil Ditutup.');
    }

    // Manajemen Fungsio
    // 1. Tampilkan Halaman Manajemen
    public function manageFungsios()
    {
        // Ambil data orang
        $fungsios = Fungsio::orderBy('created_at', 'desc')->get();
        
        // [BARU] Ambil data default divisi untuk form setting di halaman ini
        $defaults = DivisionDefault::all();
        
        return view('admin.fungsios_index', compact('fungsios', 'defaults'));
    }

    // 2. Proses Simpan Fungsio Baru
    public function storeFungsio(Request $request)
    {
        // Validasi: Nama wajib, Email wajib & tidak boleh kembar
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:fungsios,email',
            'division' => 'required|string|max:50',
        ], [
            'email.unique' => 'Email ini sudah terdaftar untuk Fungsio lain.'
        ]);

        Fungsio::create([
            'name' => $request->name,
            'email' => $request->email,
            'division' => $request->division,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Fungsio berhasil ditambahkan.');
    }

    // 3. Ubah Status Aktif/Non-Aktif
    public function toggleFungsio($id)
    {
        $fungsio = Fungsio::findOrFail($id);
        
        // Balik statusnya (true jadi false, false jadi true)
        $fungsio->is_active = !$fungsio->is_active;
        $fungsio->save();

        $status = $fungsio->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Fungsio berhasil $status.");
    }
    // ------------------------------------------------------------------
    // MANAJEMEN KUOTA PER BATCH (TARGET REAL)
    // ------------------------------------------------------------------
    // 1. Tampilkan Halaman Kelola Kuota
    public function editBatchQuotas($id)
    {
        $batch = \App\Models\Batch::with(['quotas.fungsio'])->findOrFail($id);
        
        // --- LOGIKA BARU: HITUNG REALISASI ---
        // Kita loop setiap kuota untuk mencari total qty dari order yang masuk
        foreach($batch->quotas as $quota) {
            // Hitung total quantity dari order_items milik fungsio ini di batch ini
            $totalSold = \App\Models\Order::where('batch_id', $id)
                ->where('fungsio_id', $quota->fungsio_id)
                ->where('status', '!=', 'Ditolak') // Order ditolak tidak dihitung
                ->withSum('orderItems', 'quantity') // Jumlahkan kolom quantity
                ->get()
                ->sum('order_items_sum_quantity'); // Ambil totalnya
            
            // Simpan data sementara ke objek quota untuk dipakai di View
            $quota->achieved_qty = $totalSold ?? 0;
            $quota->remaining_qty = $quota->target_qty - ($totalSold ?? 0);
        }
        // -------------------------------------
        
        // Ambil Data Default Global
        $defaults = DivisionDefault::all();
        
        // Grouping berdasarkan divisi
        $quotasByDivision = $batch->quotas->groupBy(function($item) {
            return $item->fungsio->division ?? 'Lainnya';
        });

        return view('admin.batch_quotas', compact('batch', 'defaults', 'quotasByDivision'));
    }

    // 2. Simpan Perubahan Target (Batch Ini Saja)
    public function updateBatchQuotas(Request $request, $id)
    {
        $inputs = $request->input('quotas'); // Array dari form [fungsio_id => target_baru]
        
        if($inputs) {
            foreach ($inputs as $fungsio_id => $target) {
                BatchQuota::updateOrCreate(
                    ['batch_id' => $id, 'fungsio_id' => $fungsio_id],
                    ['target_qty' => $target]
                );
            }
        }

        return redirect()->back()->with('success', 'Target kuota untuk batch ini berhasil diperbarui.');
    }

    // 3. Simpan Perubahan Default Global (Rumus Baku)
    public function updateDivisionDefaults(Request $request)
    {
        $defaults = $request->input('defaults'); // Array dari form [NamaDivisi => Nilai]
        
        if($defaults) {
            foreach ($defaults as $divName => $val) {
                DivisionDefault::updateOrCreate(
                    ['division_name' => $divName],
                    ['default_quota' => $val]
                );
            }
        }
        
        return redirect()->back()->with('success', 'Nilai default divisi berhasil disimpan. Akan berlaku untuk PO berikutnya.');
    }
}