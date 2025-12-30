@extends('admin.layout')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                {{ $activeBatch->name }}
            </h1>
            <span class="text-sm text-green-600 font-bold bg-green-100 px-2 py-0.5 rounded-full border border-green-200">
                ● Sedang Aktif
            </span>
        </div>
        
        <form action="{{ route('admin.batch.close', $activeBatch->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menutup PO ini? User tidak akan bisa pesan lagi.')">
            @csrf
            <button class="bg-red-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-red-700 transition shadow flex items-center gap-2">
                <i class="fas fa-stop-circle"></i> Tutup Periode PO
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-100">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Rekening Penerima</p>
            <p class="font-bold text-lg text-blue-900">{{ $activeBatch->bank_name }}</p>
            <p class="text-md text-gray-700 font-mono">{{ $activeBatch->bank_account_number }}</p>
            <p class="text-xs text-gray-500 mt-1">a.n {{ $activeBatch->bank_account_name }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-green-100 flex flex-col justify-center">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Grup WhatsApp</p>
                    <a href="{{ $activeBatch->whatsapp_link }}" target="_blank" class="text-green-600 font-bold hover:underline text-sm break-all">
                        Link Grup <i class="fas fa-external-link-alt text-xs ml-1"></i>
                    </a>
                </div>
                <i class="fab fa-whatsapp text-4xl text-green-100 text-green-500"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-5 rounded-xl shadow-lg flex flex-col justify-center text-center">
            <p class="text-sm opacity-80 uppercase tracking-wider font-bold">Pesanan Masuk</p>
            <h2 class="text-4xl font-extrabold mt-1">{{ count($orders) }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Validasi Pesanan Baru</h3>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">{{ count($pendingOrders) }} Pending</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="p-4">Pemesan</th>
                                <th class="p-4">Detail Menu</th>
                                <th class="p-4 text-center">Bukti</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pendingOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                                </td>
                                <td class="p-4">
                                    <ul class="list-disc pl-4 text-xs text-gray-600 space-y-1">
                                        @foreach($order->orderItems as $item)
                                            <li><span class="font-bold">{{ $item->quantity }}x</span> {{ $item->product_name_snapshot }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ asset('uploads/bukti/' . $order->payment_proof) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                                        <i class="fas fa-image"></i>
                                    </a>
                                </td>
                                <td class="p-4 text-right space-x-1">
                                    <form action="{{ route('admin.order.update', $order->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button name="status" value="Lunas" class="bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-600 hover:text-white transition">Terima</button>
                                        <button name="status" value="Ditolak" class="bg-red-100 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-400 italic bg-gray-50/50">
                                    <i class="fas fa-check-circle mb-2 block text-2xl text-gray-300"></i>
                                    Semua pesanan aman terkendali.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-bold text-gray-700">Stok Menu (Batch Ini)</h3>
                </div>
                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
                    @foreach($activeBatch->products as $product)
                    <div class="border rounded-lg p-3 relative {{ $product->pivot->is_active ? 'bg-white' : 'bg-gray-100 opacity-75' }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800">{{ $product->name }}</h4>
                                <p class="text-xs text-gray-500">Rp {{ number_format($product->pivot->price) }}</p>
                            </div>
                            
                            <form action="{{ route('admin.product.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button class="text-[10px] font-bold px-2 py-1 rounded border transition
                                    {{ $product->pivot->is_active ? 'bg-green-50 text-green-600 border-green-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200' : 'bg-red-50 text-red-600 border-red-200 hover:bg-green-50 hover:text-green-600 hover:border-green-200' }}">
                                    {{ $product->pivot->is_active ? 'Aktif' : 'Habis' }}
                                </button>
                            </form>
                        </div>
                        
                        @php
                            $percent = $product->pivot->stock > 0 ? ($product->pivot->sold / $product->pivot->stock) * 100 : 0;
                            $sisa = $product->pivot->stock - $product->pivot->sold;
                        @endphp
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-1 overflow-hidden">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-wide">
                            <span class="text-blue-600">{{ $product->pivot->sold }} Terjual</span>
                            <span class="{{ $sisa < 5 ? 'text-red-500' : 'text-gray-400' }}">Sisa: {{ $sisa }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection