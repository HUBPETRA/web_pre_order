<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Batch;
use App\Models\Fungsio;
use Illuminate\Support\Str; // untuk buat nama file acak
use Illuminate\Support\Facades\Mail; // Untuk kirim email
use App\Mail\OrderPlacedMail;

class OrderController extends Controller
{
    // STEP 1: Tampilkan Menu dari BATCH AKTIF
    public function index()
    {
        $activeBatch = Batch::where('is_active', true)
                            ->with(['products' => function($query) {
                                $query->wherePivot('is_active', true);
                            }])
                            ->first();

        if (!$activeBatch) {
            return view('po_closed'); 
        }

        $cart = session()->get('cart', []);
        
        return view('step1_menu', compact('activeBatch', 'cart'));
    }

    // STEP 2: Checkout
    public function checkout(Request $request)
    {
        $quantities = $request->input('quantities');
        // Filter item yang jumlahnya > 0
        $cartItems = array_filter($quantities, function($qty) { return $qty > 0; });

        if (empty($cartItems)) {
            return redirect()->route('step1')->with('error', 'Pilih minimal 1 item.');
        }

        $activeBatch = Batch::where('is_active', true)->with('products')->firstOrFail();

        $selectedItems = [];
        $totalPrice = 0;

        foreach ($activeBatch->products as $product) {
            if (isset($cartItems[$product->id])) {
                $qty = (int) $cartItems[$product->id]; // Pastikan integer
                
                // --- VALIDASI STOK TAHAP 1 (Saat klik Checkout) ---
                $currentStock = $product->pivot->stock;
                $currentSold = $product->pivot->sold;
                $available = $currentStock - $currentSold;

                // Jika user minta lebih dari sisa stok
                if ($qty > $available) {
                    return redirect()->route('step1')->with('error', "Maaf, stok '$product->name' hanya tersisa $available porsi.");
                }
                // --------------------------------------------------

                $price = $product->pivot->price; 
                $subtotal = $price * $qty;
                $totalPrice += $subtotal;

                $selectedItems[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal
                ];
            }
        }
        
        // Simpan keranjang yang sudah tervalidasi
        session()->put('cart', $cartItems);
        $fungsios = Fungsio::where('is_active', true)->orderBy('name', 'asc')->get(); 

        return view('step2_checkout', compact('selectedItems', 'totalPrice', 'activeBatch','fungsios'));
    }

    // STEP 3: Simpan Order 
    public function store(Request $request)
    {
        // 1. Validasi (Security Layer 1: Filter Input)
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|numeric',
            'fungsio_id' => 'required|exists:fungsios,id', // [BARU] Wajib pilih Fungsio
            'payment_proof' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpeg,png,jpg',
                    'max:2048',
            ],
        ]);

        $activeBatch = \App\Models\Batch::where('is_active', true)->with('products')->first();
        if(!$activeBatch) return redirect()->back()->with('error', 'Maaf, PO baru saja ditutup.');

        $cart = session()->get('cart', []);

        // --- VALIDASI STOK TAHAP 2 (CRITICAL CHECK) ---
        // Sekalian hitung Total Amount di sini biar efisien
        $calculatedTotalAmount = 0; 

        foreach($activeBatch->products as $product) {
            if(isset($cart[$product->id]) && $cart[$product->id] > 0) {
                $qty = $cart[$product->id];
                $available = $product->pivot->stock - $product->pivot->sold;

                if ($qty > $available) {
                    return redirect()->route('step1')->with('error', "Gagal memproses! Stok '$product->name' tidak mencukupi (Sisa: $available).");
                }

                // [BARU] Hitung total belanja untuk disimpan di header order
                $calculatedTotalAmount += ($product->pivot->price * $qty);
            }
        }
        // -------------------------------------------------------------

        // 2. Upload Gambar
        $file = $request->file('payment_proof');
        $randomName = \Illuminate\Support\Str::random(32) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/bukti'), $randomName);

        // 3. Buat Header Order
        $order = \App\Models\Order::create([
            'batch_id' => $activeBatch->id,
            'customer_name' => strip_tags($request->customer_name),
            'customer_phone' => $request->customer_phone,
            
            'fungsio_id' => $request->fungsio_id, // [BARU] INI KUNCINYA AGAR KUOTA OTOMATIS BERKURANG
            'total_amount' => $calculatedTotalAmount, // [BARU] Simpan total nominal
            
            'payment_proof' => $randomName,
            'status' => 'Menunggu Verifikasi'
        ]);

        // 4. Simpan Detail Item & Update Stok
        foreach($activeBatch->products as $product) {
            if(isset($cart[$product->id]) && $cart[$product->id] > 0) {
                
                $qty = $cart[$product->id];
                
                // Kurangi Stok (Update Sold)
                $currentSold = $product->pivot->sold;
                $activeBatch->products()->updateExistingPivot($product->id, [
                    'sold' => $currentSold + $qty
                ]);

                // Simpan OrderItem
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'quantity' => $qty,
                    'price_snapshot' => $product->pivot->price,
                    'subtotal' => $product->pivot->price * $qty
                ]);
            }
        }

        // --- [BARU] KIRIM EMAIL NOTIFIKASI ---
        // try {
        //     // Cek apakah user mengisi email (berjaga-jaga)
        //     if($order->customer_email) {
        //         Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
        //     }
        // } catch (\Exception $e) {
        //     // Jika gagal kirim email (misal internet mati), jangan biarkan sistem error.
        //     // Cukup catat di log, tapi biarkan user lanjut ke halaman sukses.
        //     \Log::error("Gagal kirim email order #{$order->id}: " . $e->getMessage());
        // }

        session()->forget('cart');
        
        // Redirect ke route success
        return redirect()->route('success');
    }

    public function success()
    {
        $activeBatch = \App\Models\Batch::where('is_active', true)->first();
        return view('step3_success', compact('activeBatch'));
    }
}