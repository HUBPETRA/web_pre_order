<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Batch;
use App\Models\Order;
use App\Models\Product;

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
        $batch = \App\Models\Batch::with('products')->findOrFail($id);

        $orders = \App\Models\Order::where('batch_id', $id)
                    ->with('orderItems')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('admin.dashboard_archive', compact('batch', 'orders'));
    }

    // Halaman Grafik & List History
    public function analytics(Request $request)
    {
        // Grafik
        $batchesForChart = Batch::orderBy('created_at', 'asc')->get();
        $chartLabels = [];
        $chartData = [];
        foreach ($batchesForChart as $batch) {
            $lunasCount = $batch->orders()->where('status', 'Lunas')->count();
            $chartLabels[] = $batch->name;
            $chartData[] = $lunasCount;
        }

        // 2. Logika Query Tabel
        $query = Batch::with('products')->orderBy('created_at', 'desc');

        if ($request->has('q') && !empty($request->q)) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhereHas('products', function($subQuery) use ($keyword) {
                      $subQuery->where('name', 'LIKE', "%{$keyword}%");
                  });
            });
        }

        $batches = $query->paginate(10);

        //Search bar AJAX 
        if ($request->ajax()) {
            return view('admin.partials.batch_table', compact('batches'))->render();
        }

        return view('admin.dashboard_analytics', compact('batches', 'chartLabels', 'chartData'));
    }

    // PEMBUATAN BATCH BARU


    // STEP 1: Halaman Input Info Dasar
    public function createBatchStep1()
    {
        if (Batch::where('is_active', true)->exists()) {
            return redirect()->route('admin.dashboard')->with('error', 'Harap tutup PO aktif sebelum membuat baru.');
        }
        return view('admin.create_batch_step1');
    }

    // Simpan Draft Batch
    public function storeBatchStep1(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'bank_name' => 'required',
            'bank_account_number' => 'required',
            'bank_account_name' => 'required',
            'whatsapp_link' => 'required|url',
        ]);

        // Buat Batch 
        $batch = Batch::create([
            'name' => $request->name,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'whatsapp_link' => $request->whatsapp_link,
            'is_active' => false, 
        ]);

        
        return redirect()->route('admin.batch.step2', $batch->id);
    }

    // STEP 2: Halaman Kelola Produk
    public function createBatchStep2($id)
    {
        $batch = Batch::with('products')->findOrFail($id);
        return view('admin.create_batch_step2', compact('batch'));
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

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
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

    // Tutup Batch 
    public function closeBatch($id)
    {
        $batch = Batch::findOrFail($id);
        $batch->update(['is_active' => false]);
        return redirect()->route('admin.analytics')->with('success', 'Batch PO Berhasil Ditutup.');
    }
}