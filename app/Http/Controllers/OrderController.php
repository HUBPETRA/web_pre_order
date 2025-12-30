<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Batch;

class OrderController extends Controller
{
    // STEP 1: Tampilkan Menu dari BATCH AKTIF
    public function index()
    {
        $activeBatch = Batch::where('is_active', true)
                            ->with(['products' => function($query) {
                                // Ambil produk yang di-set aktif di batch ini
                                $query->wherePivot('is_active', true);
                            }])
                            ->first();

        // Jika tidak ada PO aktif, tampilkan pesan tutup
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

        // Simpan keranjang sementara di session
        session()->put('cart', $quantities);


        $activeBatch = Batch::where('is_active', true)->with('products')->firstOrFail();

        $selectedItems = [];
        $totalPrice = 0;

        // Hitung total harga
        foreach ($activeBatch->products as $product) {
            if (isset($cartItems[$product->id])) {
                $qty = $cartItems[$product->id];
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

        // Kirim $activeBatch ke View agar bisa menampilkan Bank & Rekening
        return view('step2_checkout', compact('selectedItems', 'totalPrice', 'activeBatch'));
    }

    // STEP 3: Simpan Order 
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $activeBatch = Batch::where('is_active', true)->first();
        if(!$activeBatch) return redirect()->back()->with('error', 'Maaf, PO baru saja ditutup.');

        // 1. Upload Gambar
        $imageName = time().'.'.$request->payment_proof->extension();  
        $request->payment_proof->move(public_path('uploads/bukti'), $imageName);

        // 2. Buat Header Order
        $order = Order::create([
            'batch_id' => $activeBatch->id, // Relasi ke Batch
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'payment_proof' => $imageName,
            'status' => 'Menunggu Verifikasi'
        ]);

        // 3. Simpan Detail Item (Looping keranjang dari session/request)
        $cart = session()->get('cart', []);
        $products = $activeBatch->products; 
        foreach($products as $product) {
            if(isset($cart[$product->id]) && $cart[$product->id] > 0) {
                
                $qty = $cart[$product->id];
                
                // Kurangi Stok di Pivot Table
                $currentSold = $product->pivot->sold;
                $activeBatch->products()->updateExistingPivot($product->id, [
                    'sold' => $currentSold + $qty
                ]);

                // Simpan ke OrderItem
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name, // Nama saat ini
                    'quantity' => $qty,
                    'price_snapshot' => $product->pivot->price, // Harga saat ini
                    'subtotal' => $product->pivot->price * $qty
                ]);
            }
        }

        session()->forget('cart');
        return view('step3_success', ['whatsapp_link' => $activeBatch->whatsapp_link]);
    }

    public function success()
    {
        // Ambil data batch aktif
        $activeBatch = \App\Models\Batch::where('is_active', true)->first();

        return view('step3_success', compact('activeBatch'));
    }
}