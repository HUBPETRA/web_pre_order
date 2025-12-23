<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;

class AdminController extends Controller
{
    // --- AUTHENTICATION ---
    
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

    // --- DASHBOARD & ORDER ---

    public function dashboard()
    {
        // 1. Ambil Order
        $orders = Order::orderBy('created_at', 'desc')->get();
        
        // 2. Ambil Produk
        $products = Product::all();

        // 3. LOGIKA BARU: Hitung Total Menu yang LUNAS
        $lunasOrders = Order::where('status', 'Lunas')->get();
        $summary = [];

        foreach ($lunasOrders as $order) {
            $details = json_decode($order->order_details);
            foreach ($details as $item) {
                if (isset($summary[$item->name])) {
                    $summary[$item->name] += $item->qty;
                } else {
                    $summary[$item->name] = $item->qty;
                }
            }
        }
        // $summary sekarang isinya: ['Ayam' => 15, 'Babi' => 5]

        return view('admin.dashboard', compact('orders', 'products', 'summary'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Status pesanan diperbarui.');
    }

    // --- MANAJEMEN PRODUK ---

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
        ]);

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function toggleProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]); // Switch True/False
        
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Menu berhasil $status.");
    }
}