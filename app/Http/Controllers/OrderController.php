<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Batch;
use App\Models\Fungsio;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; // [PENTING] Untuk Database Transaction
use Illuminate\Support\Facades\Log;
use App\Mail\OrderPlacedMail;


class OrderController extends Controller
{
    /**
     * STEP 1: Tampilkan Menu dari BATCH AKTIF
     */
    public function index()
    {
        // Ambil batch aktif beserta produk yang juga aktif
        $activeBatch = Batch::where('is_active', true)
            ->with(['products' => function($query) {
                $query->wherePivot('is_active', true);
            }])
            ->first();

        // Jika tidak ada batch aktif, tampilkan halaman tutup
        if (!$activeBatch) {
            return view('po_closed'); 
        }

        $cart = session()->get('cart', []);
        $banner_image = $activeBatch->banner_image;

        return view('step1_menu', compact('activeBatch', 'cart','banner_image'));
    }

    /**
     * STEP 2: Validasi Keranjang & Checkout View
     */
    public function checkout(Request $request)
    {
        $quantities = $request->input('quantities', []);
        
        // Filter item yang jumlahnya > 0
        $cartItems = array_filter($quantities, fn($qty) => $qty > 0);

        if (empty($cartItems)) {
            return redirect()->route('step1')->with('error', 'Pilih minimal 1 item.');
        }

        $activeBatch = Batch::where('is_active', true)->with('products')->firstOrFail();

        $selectedItems = [];
        $totalPrice = 0;

        foreach ($activeBatch->products as $product) {
            if (isset($cartItems[$product->id])) {
                $qty = (int) $cartItems[$product->id];
                
                // --- VALIDASI STOK TAHAP 1 (Preview) ---
                $available = $product->pivot->stock - $product->pivot->sold;

                if ($qty > $available) {
                    return redirect()->route('step1')
                        ->with('error', "Maaf, stok '$product->name' hanya tersisa $available porsi.");
                }

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
        
        // Simpan keranjang sementara ke session
        session()->put('cart', $cartItems);
        
        // Ambil data fungsio untuk dropdown
        $fungsios = Fungsio::where('is_active', true)->orderBy('name', 'asc')->get(); 

        return view('step2_checkout', compact('selectedItems', 'totalPrice', 'activeBatch', 'fungsios'));
    }

    /**
     * STEP 3: Proses Simpan Order (Database Transaction)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|numeric',
            'customer_email' => 'required|email',
            'fungsio_id'     => 'required|exists:fungsios,id',
            'payment_proof'  => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $activeBatch = Batch::where('is_active', true)->with('products')->first();
        if (!$activeBatch) {
            return redirect()->back()->with('error', 'Maaf, PO baru saja ditutup.');
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('step1')->with('error', 'Keranjang belanja kosong.');
        }

        // 2. Upload Gambar (Dilakukan sebelum transaksi DB)
        try {
            $file = $request->file('payment_proof');
            $randomName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            
            // Simpan di folder privat (storage/app/transfers)
            $file->storeAs('transfers', $randomName); 

        } catch (\Exception $e) {
            Log::error("Gagal upload file: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupload bukti pembayaran.');
        }

        // 3. Database Transaction (Critical Section)
        // Menggunakan transaction agar jika insert item gagal, header order & stok dibatalkan otomatis
        try {
            $order = DB::transaction(function () use ($request, $activeBatch, $cart, $randomName) {
                
                // A. Hitung Total & Validasi Stok Final (Locking logic simulation)
                $calculatedTotalAmount = 0;
                $itemsToInsert = [];

                foreach ($activeBatch->products as $product) {
                    if (isset($cart[$product->id]) && $cart[$product->id] > 0) {
                        $qty = $cart[$product->id];
                        $available = $product->pivot->stock - $product->pivot->sold;

                        // Cek Stok Terakhir
                        if ($qty > $available) {
                            throw new \Exception("Stok '$product->name' tidak mencukupi (Sisa: $available).");
                        }

                        $subtotal = $product->pivot->price * $qty;
                        $calculatedTotalAmount += $subtotal;

                        // Siapkan data untuk insert nanti
                        $itemsToInsert[] = [
                            'product' => $product,
                            'qty' => $qty,
                            'subtotal' => $subtotal
                        ];
                    }
                }

                // B. Buat Header Order
                $order = Order::create([
                    'batch_id'       => $activeBatch->id,
                    'customer_name'  => strip_tags($request->customer_name),
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'fungsio_id'     => $request->fungsio_id,
                    'total_amount'   => $calculatedTotalAmount,
                    'payment_proof'  => $randomName,
                    'status'         => 'Menunggu Verifikasi'
                ]);

                // C. Simpan Detail Item & Update Stok
                foreach ($itemsToInsert as $item) {
                    // Update Stok (Sold Increment)
                    $product = $item['product'];
                    $qty = $item['qty'];

                    $activeBatch->products()->updateExistingPivot($product->id, [
                        'sold' => $product->pivot->sold + $qty
                    ]);

                    // Create Order Item
                    OrderItem::create([
                        'order_id'              => $order->id,
                        'product_id'            => $product->id,
                        'product_name_snapshot' => $product->name,
                        'quantity'              => $qty,
                        'price_snapshot'        => $product->pivot->price,
                        'subtotal'              => $item['subtotal']
                    ]);
                }

                return $order;
            });

        } catch (\Exception $e) {
            // Jika ada error stok/database, hapus file gambar yang terlanjur diupload agar tidak nyampah
            if (Storage::exists('transfers/' . $randomName)) {
                Storage::delete('transfers/' . $randomName);
            }

            return redirect()->route('step1')->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }

        // 4. Kirim Email Notifikasi (Di luar transaction agar tidak memperlambat DB)
        try {
            if ($order->customer_email) {
                Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
            }
        } catch (\Exception $e) {
            Log::error("Gagal kirim email order #{$order->id}: " . $e->getMessage());
            // Lanjut saja, jangan gagalkan pesanan hanya karena email error
        }

        // 5. Bersihkan Session & Redirect
        session()->forget('cart');
        session()->put('last_order_id', $order->id);
        return redirect()->route('success');
    }

    /**
     * STEP 4: Halaman Sukses
     */
    public function success()
    {
        $activeBatch = Batch::where('is_active', true)->first();

        $orderId = session('last_order_id');
        
        if (!$orderId) {
            return redirect()->route('step1');
        }

        $order = Order::with('orderItems')->find($orderId);

        return view('step3_success', compact('activeBatch', 'order'));
    }
}