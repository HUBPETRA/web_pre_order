<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Dapur Enak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { bg-gray-100; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen">

    <nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 text-white p-2 rounded-lg">
                    <i class="fas fa-utensils"></i>
                </div>
                <h1 class="font-bold text-xl text-slate-800 tracking-tight">Admin Dashboard</h1>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="text-sm font-medium text-slate-500 hover:text-red-600 transition flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-7xl">

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8 rounded-r shadow-sm flex justify-between items-center animate-pulse">
                <span><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-800 hover:text-green-900">&times;</button>
            </div>
        @endif

        <div class="mb-10">
            <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-blue-500"></i> Rekap Masakan (Verified Only)
            </h2>
            
            @if(count($summary) > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($summary as $menuName => $qty)
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition border-l-4 border-l-blue-500">
                        <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Total</p>
                        <h3 class="font-bold text-slate-800 text-sm truncate" title="{{ $menuName }}">{{ $menuName }}</h3>
                        <p class="text-2xl font-extrabold text-blue-700 mt-2">{{ $qty }} <span class="text-xs font-normal text-slate-400">Porsi</span></p>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-6 rounded-xl border border-dashed border-slate-300 text-center text-slate-500">
                    Belum ada pesanan yang lunas hari ini.
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
                    <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center sticky top-0">
                        <h2 class="font-bold text-lg text-slate-700"><i class="fas fa-clipboard-list mr-2 text-blue-500"></i> Pesanan Masuk</h2>
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">{{ count($orders) }} Total</span>
                    </div>
                    
                    <div class="overflow-x-auto custom-scrollbar max-h-[600px] overflow-y-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="p-4 whitespace-nowrap">Pemesan</th>
                                    <th class="p-4">Detail Menu</th>
                                    <th class="p-4 text-center">Bukti</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-100">
                                @forelse($orders as $order)
                                <tr class="hover:bg-slate-50 transition group">
                                    <td class="p-4 align-top">
                                        <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                            <i class="fab fa-whatsapp"></i> {{ $order->customer_phone }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1">{{ $order->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <ul class="space-y-1">
                                            @foreach(json_decode($order->order_details) as $item)
                                                <li class="flex items-center justify-between text-xs bg-slate-50 p-1 rounded border border-slate-100">
                                                    <span>{{ $item->name }}</span>
                                                    <span class="font-bold bg-white px-1 rounded shadow-sm">x{{ $item->qty }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="p-4 align-top text-center">
                                        <a href="{{ asset('uploads/bukti/' . $order->payment_proof) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 hover:scale-110 transition border border-blue-100" title="Lihat Bukti">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    </td>
                                    <td class="p-4 align-top text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border
                                            {{ $order->status == 'Lunas' ? 'bg-green-50 text-green-700 border-green-200' : 
                                               ($order->status == 'Ditolak' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-yellow-50 text-yellow-700 border-yellow-200') }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 align-top text-right">
                                        @if($order->status == 'Menunggu Verifikasi')
                                        <form action="{{ route('admin.order.update', $order->id) }}" method="POST" class="flex gap-2 justify-end opacity-50 group-hover:opacity-100 transition">
                                            @csrf
                                            <button name="status" value="Lunas" class="bg-green-500 text-white w-8 h-8 rounded-lg hover:bg-green-600 shadow-sm flex items-center justify-center transition" title="Terima / Lunas">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button name="status" value="Ditolak" class="bg-red-500 text-white w-8 h-8 rounded-lg hover:bg-red-600 shadow-sm flex items-center justify-center transition" title="Tolak Pesanan">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @else
                                            <span class="text-slate-300 text-xs italic">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-slate-400">Belum ada pesanan masuk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-blue-500"></i> Menu Baru
                    </h2>
                    <form action="{{ route('admin.product.store') }}" method="POST">
                        @csrf
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase">Nama Menu</label>
                                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Cth: Sate Ayam" required>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase">Harga</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                                    <input type="number" name="price" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="15000" required>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase">Deskripsi Singkat</label>
                                <textarea name="description" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" rows="2" placeholder="Isian..." required></textarea>
                            </div>
                            <button class="w-full bg-slate-800 text-white font-bold py-3 rounded-lg hover:bg-slate-900 transition flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i> Simpan Menu
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-white px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-lg text-slate-700 flex items-center gap-2">
                            <i class="fas fa-utensils text-blue-500"></i> Kelola Menu
                        </h2>
                    </div>
                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar p-2">
                        @foreach($products as $product)
                        <div class="flex items-center justify-between p-3 mb-2 bg-white border border-slate-100 rounded-lg hover:shadow-md transition group">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-slate-400 bg-slate-100 shrink-0">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-700 text-sm truncate {{ !$product->is_active ? 'line-through text-slate-400' : '' }}">{{ $product->name }}</h4>
                                    <p class="text-xs text-slate-500">Rp {{ number_format($product->price) }}</p>
                                </div>
                            </div>
                            
                            <form action="{{ route('admin.product.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button class="w-8 h-8 rounded-full flex items-center justify-center transition shadow-sm
                                    {{ $product->is_active ? 'bg-red-100 text-red-500 hover:bg-red-500 hover:text-white' : 'bg-green-100 text-green-500 hover:bg-green-500 hover:text-white' }}"
                                    title="{{ $product->is_active ? 'Nonaktifkan (Habis)' : 'Aktifkan Kembali' }}">
                                    <i class="fas {{ $product->is_active ? 'fa-power-off' : 'fa-check' }}"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>
</body>
</html>