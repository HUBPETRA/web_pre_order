<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    // STEP 1: Tampilkan Menu
    public function index()
    {

        $products = Product::where('is_active', true)->get();

        // Ambil keranjang dari session
        $cart = session()->get('cart', []);

        // PERBAIKAN DI SINI: Memanggil 'step1_menu', bukan 'order_form'
        return view('step1_menu', compact('products', 'cart'));
    }

    // STEP 2: Checkout (Terima Pilihan Menu)
    public function checkout(Request $request)
    {
        $quantities = $request->input('quantities');
        
        // Filter hanya item yang jumlahnya > 0
        // array_filter akan membuang item yang value-nya 0 atau null
        $cartItems = array_filter($quantities, function($qty) {
            return $qty > 0;
        });

        if (empty($cartItems)) {
            return redirect()->route('step1')->with('error', 'Pilih minimal 1 item.');
        }

        // Simpan ke Session
        session()->put('cart', $quantities);

        // AMBIL DATA ASLI DARI DATABASE
        // Kita cari produk yang ID-nya ada di daftar pesanan user
        $products = Product::whereIn('id', array_keys($cartItems))->get();

        $selectedItems = [];
        $totalPrice = 0;

        foreach ($products as $product) {
            $qty = $cartItems[$product->id];
            $subtotal = $product->price * $qty;
            $totalPrice += $subtotal;

            $selectedItems[] = [
                'id' => $product->id,
                'name' => $product->name, // Ini sekarang ambil nama ASLI dari DB
                'qty' => $qty,
                'price' => $product->price,
                'subtotal' => $subtotal
            ];
        }

        return view('step2_checkout', compact('selectedItems', 'totalPrice'));
    }

    // STEP 3: Proses Simpan Order
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload File
        $imageName = time().'.'.$request->payment_proof->extension();  
        $request->payment_proof->move(public_path('uploads/bukti'), $imageName);

        // Simpan Order
        Order::create([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'order_details' => $request->order_details, // Langsung simpan string JSON
            'status' => 'Menunggu Verifikasi',
            'payment_proof' => $imageName,
        ]);

        // Hapus session cart setelah sukses
        session()->forget('cart');

        return view('step3_success');
    }
    
    // Opsional: Route redirect untuk 'success' jika diakses langsung
    public function success() {
        return view('step3_success');
    }
}