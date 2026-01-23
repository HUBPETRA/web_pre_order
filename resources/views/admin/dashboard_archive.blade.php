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

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Modal Awal</p>
            <h2 class="text-xl font-bold text-slate-700">Rp {{ number_format($financials['modal'], 0, ',', '.') }}</h2>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Total Pemasukan</p>
            <h2 class="text-xl font-bold text-blue-600">Rp {{ number_format($financials['total_income'], 0, ',', '.') }}</h2>
            <p class="text-[10px] text-gray-400">Penjualan + Denda</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Keuntungan Bersih</p>
            <h2 class="text-2xl font-extrabold {{ $financials['profit'] >= 0 ? 'text-green-600' : 'text-red-500' }}">
                Rp {{ number_format($financials['profit'], 0, ',', '.') }}
            </h2>
            @if($financials['profit'] < 0)
                <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-bold">RUGI</span>
            @else
                <span class="text-[10px] bg-green-100 text-green-600 px-2 py-0.5 rounded font-bold">PROFIT</span>
            @endif
        </div>

        <div class="bg-blue-50 p-5 rounded-xl border border-blue-100">
            <p class="text-xs text-blue-500 font-bold uppercase mb-1">Total Order Lunas</p>
            <h2 class="text-xl font-bold text-blue-800">{{ $orders->where('status', 'Lunas')->count() }} <span class="text-sm font-normal">Transaksi</span></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <h3 class="font-bold text-gray-700">Rincian Data Pesanan</h3>
                    
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="live-search" 
                               placeholder="Cari Nama / Email / HP..." 
                               class="w-full md:w-64 pl-9 pr-8 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                        
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <i id="search-loading" class="fas fa-spinner fa-spin absolute right-3 top-1/2 -translate-y-1/2 text-blue-500 hidden"></i>
                    </div>
                </div>
                
                <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Pemesan</th>
                                <th class="p-4">Item Dibeli</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Pengambilan</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="divide-y divide-gray-100 bg-white">
                            @include('admin.partials.order_rows')
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-medal text-yellow-500"></i> Laporan Pencapaian & Denda
                    </h3>
                </div>
                
                <div class="p-6">
                    @foreach($quotasByDivision as $division => $quotas)
                    <div class="mb-6 last:mb-0">
                        <h4 class="font-bold text-xs uppercase text-gray-500 mb-3 border-b border-gray-100 pb-1">{{ $division }}</h4>
                        
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($quotas as $q)
                            <div class="flex items-center justify-between border border-gray-100 p-3 rounded-lg hover:bg-gray-50 transition">
                                
                                <div class="w-1/3">
                                    <p class="font-bold text-sm text-slate-700">{{ $q->fungsio->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                            Target: {{ $q->target_qty }}
                                        </span>
                                        @if($q->deficit > 0)
                                            <span class="text-[10px] text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100 font-bold">
                                                Kurang: {{ $q->deficit }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-center w-1/4">
                                    <div class="text-lg font-bold {{ $q->deficit <= 0 ? 'text-green-600' : 'text-blue-600' }}">
                                        {{ $q->achieved_qty }}
                                    </div>
                                    <div class="text-[9px] uppercase text-gray-400 font-bold tracking-wider">Terjual</div>
                                </div>

                                <div class="text-right w-1/3">
                                    @if($q->deficit > 0)
                                        {{-- TAMPILAN DENDA --}}
                                        <div class="font-mono font-bold text-red-600 text-sm mb-1">
                                            Rp {{ number_format($q->fine_amount) }}
                                        </div>
                                        
                                        {{-- TOMBOL TOGGLE BAYAR DENDA --}}
                                        <form action="{{ route('admin.quota.toggle_fine', $q->id) }}" method="POST">
                                            @csrf
                                            @if($q->is_fine_paid)
                                                <button type="submit" class="bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded text-[10px] font-bold hover:bg-green-200 transition flex items-center gap-1 ml-auto" title="Klik untuk batalkan">
                                                    <i class="fas fa-check-double"></i> Lunas
                                                </button>
                                            @else
                                                <button type="submit" class="bg-red-100 text-red-600 border border-red-200 px-2 py-1 rounded text-[10px] font-bold hover:bg-red-600 hover:text-white transition flex items-center gap-1 ml-auto animate-pulse" title="Klik untuk tandai lunas">
                                                    <i class="fas fa-hand-holding-usd"></i> Bayar
                                                </button>
                                            @endif
                                        </form>

                                    @else
                                        {{-- TAMPILAN AMAN --}}
                                        <div class="flex flex-col items-end">
                                            <div class="text-green-500 bg-green-100 w-8 h-8 rounded-full flex items-center justify-center mb-1">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <span class="text-[10px] text-green-600 font-bold">Aman</span>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // 1. CHART JS
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('productChart').getContext('2d');
        const labels = {!! json_encode($chartLabels) !!};
        const dataSold = {!! json_encode($chartData) !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Porsi Terjual',
                    data: dataSold,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.7
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true, grid: { display: false }, ticks: { stepSize: 1 } },
                    y: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    });

    // 2. LIVE SEARCH (AJAX)
    $(document).ready(function() {
        let timeout = null;
        
        $('#live-search').on('keyup', function() {
            clearTimeout(timeout); 
            
            let query = $(this).val();
            let loading = $('#search-loading');
            
            loading.removeClass('hidden'); 
            
            // Debounce 500ms
            timeout = setTimeout(function() {
                $.ajax({
                    url: "{{ url()->current() }}", 
                    method: "GET",
                    data: { q: query },
                    success: function(response) {
                        $('#table-body').html(response);
                        loading.addClass('hidden');
                    },
                    error: function() {
                        loading.addClass('hidden');
                        console.error('Gagal mengambil data pencarian.');
                    }
                });
            }, 500);
        });
    });
</script>
@endpush