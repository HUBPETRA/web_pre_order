@extends('admin.layout')

@section('content')
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.analytics') }}" class="w-10 h-10 flex items-center justify-center bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-blue-600 hover:border-blue-300 transition shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800">{{ $batch->name }}</h1>
                
                @if($batch->is_active)
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold border border-green-200 animate-pulse">
                        <i class="fas fa-circle text-[8px] mr-1"></i> Aktif
                    </span>
                @else
                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded font-bold border border-gray-300">
                        <i class="fas fa-archive mr-1"></i> Arsip
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500">Dibuat pada: {{ $batch->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Total Pesanan</p>
            <h2 class="text-3xl font-extrabold text-slate-800">{{ count($orders) }}</h2>
            <p class="text-xs text-gray-400 mt-1">Transaksi tercatat</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Pesanan Lunas</p>
            <h2 class="text-3xl font-extrabold text-green-600">{{ $orders->where('status', 'Lunas')->count() }}</h2>
            <p class="text-xs text-gray-400 mt-1">Transaksi berhasil</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Dibatalkan</p>
            <h2 class="text-3xl font-extrabold text-red-500">{{ $orders->where('status', 'Ditolak')->count() }}</h2>
            <p class="text-xs text-gray-400 mt-1">Transaksi gagal/ditolak</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Rincian Data Pesanan</h3>
                </div>
                
                <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Pemesan</th>
                                <th class="p-4">Item Dibeli</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($orders as $order)
                            <tr class="hover:bg-blue-50 transition group">
                                <td class="p-4 text-gray-500 text-xs align-top w-24">
                                    {{ $order->created_at->format('d/m/Y') }} <br>
                                    <span class="text-[10px]">{{ $order->created_at->format('H:i') }}</span>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500 flex items-center gap-1">
                                        <i class="fab fa-whatsapp text-green-500"></i> 
                                        <a href="https://wa.me/{{ $order->customer_phone }}" target="_blank" class="hover:underline hover:text-green-600">
                                            {{ $order->customer_phone }}
                                        </a>
                                    </div>
                                    <div class="mt-1 text-[10px] text-blue-500 bg-blue-50 inline-block px-1 rounded">
                                        Ref: {{ $order->fungsio->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="p-4 align-top">
                                    <ul class="list-none space-y-1 text-xs text-gray-600">
                                        @foreach($order->orderItems as $item)
                                            <li class="flex items-start gap-1">
                                                <span class="font-bold text-slate-800 min-w-[20px]">{{ $item->quantity }}x</span> 
                                                <span>{{ $item->product_name_snapshot }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="p-4 text-center align-top">
                                    @if($order->status == 'Lunas')
                                        <span class="bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Lunas</span>
                                    @elseif($order->status == 'Ditolak')
                                        <span class="bg-red-100 text-red-700 border border-red-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Ditolak</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-700 border border-yellow-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-12 text-center text-gray-400 italic bg-slate-50">
                                    <i class="far fa-folder-open text-3xl mb-2 opacity-50"></i> <br>
                                    Belum ada data pesanan masuk.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-medal text-yellow-500"></i> Laporan Pencapaian Target Fungsio
                    </h3>
                </div>
                
                <div class="p-6">
                    @foreach($quotasByDivision as $division => $quotas)
                    <div class="mb-6 last:mb-0">
                        <h4 class="font-bold text-xs uppercase text-gray-500 mb-3 border-b border-gray-100 pb-1">{{ $division }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($quotas as $q)
                            <div class="flex items-center justify-between border border-gray-100 p-3 rounded-lg hover:bg-gray-50 transition">
                                <div>
                                    <p class="font-bold text-sm text-slate-700">{{ $q->fungsio->name }}</p>
                                    <p class="text-[10px] text-gray-400">Target: {{ $q->target_qty }} Porsi</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $q->achieved_qty >= $q->target_qty ? 'text-green-600' : 'text-blue-600' }}">
                                        {{ $q->achieved_qty }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">Terjual</div>
                                </div>
                                <div class="w-12 h-12 flex items-center justify-center ml-2">
                                    @if($q->achieved_qty >= $q->target_qty)
                                        <div class="text-green-500 bg-green-100 w-8 h-8 rounded-full flex items-center justify-center" title="Target Tercapai">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    @else
                                        <div class="text-gray-300 text-xs font-bold" title="Belum Target">
                                            {{ intval(($q->achieved_qty / max(1, $q->target_qty)) * 100) }}%
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        <div class="lg:col-span-1 space-y-6"> 
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-bold text-gray-700 mb-4 text-sm flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-500"></i> Grafik Menu Terlaris
                </h3>
                <div class="relative h-48 w-full">
                    <canvas id="productChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-bold text-gray-700">Rekap Produk Terjual</h3>
                </div>
                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto custom-scrollbar">
                    @foreach($batch->products as $product)
                    <div class="border border-gray-100 rounded-lg p-3 bg-white hover:shadow-sm transition">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800 line-clamp-1" title="{{ $product->name }}">{{ $product->name }}</h4>
                                <p class="text-xs text-gray-500">@ Rp {{ number_format($product->pivot->price) }}</p>
                            </div>
                            <div class="text-right">
                                <span class="block text-lg font-bold text-blue-600 leading-none">{{ $product->pivot->sold }}</span>
                                <span class="text-[10px] text-gray-400 uppercase font-bold">Porsi</span>
                            </div>
                        </div>
                        
                        @php
                            // Untuk arsip, kita bandingkan sold vs stok awal saat itu
                            $total_stock = $product->pivot->stock;
                            $sold = $product->pivot->sold;
                            $percent = $total_stock > 0 ? ($sold / $total_stock) * 100 : 0;
                            $barColor = 'bg-blue-500';
                            if($percent >= 90) $barColor = 'bg-red-500'; 
                            elseif($percent >= 70) $barColor = 'bg-yellow-500';
                        @endphp
                        
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden relative">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('productChart').getContext('2d');
        
        // Data dari Controller
        const labels = {!! json_encode($chartLabels) !!};
        const dataSold = {!! json_encode($chartData) !!};

        new Chart(ctx, {
            type: 'bar', // Grafik Batang
            data: {
                labels: labels,
                datasets: [{
                    label: 'Porsi Terjual',
                    data: dataSold,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)', // Warna Biru
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.7
                }]
            },
            options: {
                indexAxis: 'y', // MEMBUAT GRAFIK HORIZONTAL (Nama menu di kiri)
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { display: false, drawBorder: false },
                        ticks: { stepSize: 1 }
                    },
                    y: {
                        grid: { display: false, drawBorder: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.raw + ' Porsi';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush