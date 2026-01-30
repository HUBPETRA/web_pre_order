@extends('admin.layout')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Analitik & Riwayat</h1>
            <p class="text-gray-500">Performa Penjualan dan Arsip Kegiatan.</p>
        </div>
        
        <div class="relative w-full md:w-auto">
            <span class="absolute left-3 top-2.5 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" id="live-search" value="{{ request('q') }}" 
                   class="w-full md:w-64 pl-10 pr-10 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition" 
                   placeholder="Ketik untuk mencari..." autocomplete="off">
            
            <div id="search-loading" class="absolute right-3 top-2.5 hidden">
                <i class="fas fa-circle-notch fa-spin text-blue-500"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
        <h3 class="font-bold text-lg mb-4 text-slate-700">Grafik Keuntungan & Pesanan</h3>
        <div class="relative h-64 w-full">
            {{-- PERBAIKAN 1: ID Canvas disamakan dengan Javascript --}}
            <canvas id="analyticsChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-bold text-slate-700">Daftar Kegiatan Pre-Order</h3>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="p-4">Nama Kegiatan / Menu</th>
                    <th class="p-4 hidden sm:table-cell">Tanggal</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Detail</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-gray-100" id="table-body">
                {{-- Memanggil partial saat pertama kali load --}}
                @include('admin.partials.batch_table', ['batches' => $batches])
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
{{-- PERBAIKAN 2: Pastikan Library Chart.js dimuat --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Gunakan Event Listener agar script jalan setelah HTML siap
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- 1. SCRIPT GRAFIK ---
        const chartCanvas = document.getElementById('analyticsChart'); // ID ini sekarang cocok dengan HTML

        if (chartCanvas) {
            const ctx = chartCanvas.getContext('2d');
            
            // Gunakan operator '?? []' agar tidak error jika data dari controller null
            const labels = {!! json_encode($chartLabels ?? []) !!};
            const profitData = {!! json_encode($profitData ?? []) !!};
            const soldData = {!! json_encode($soldData ?? []) !!};

            new Chart(ctx, {
                type: 'bar', 
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Keuntungan Bersih (Rp)',
                            data: profitData,
                            type: 'line', 
                            borderColor: 'rgb(34, 197, 94)', 
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 3,
                            yAxisID: 'y1', 
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Total Produk Terjual (Pcs)',
                            data: soldData,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)', 
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1,
                            yAxisID: 'y', 
                            barPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Agar grafik pas di container
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Jumlah (Pcs)' },
                            grid: { display: false } 
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Keuntungan (Rp)' },
                            grid: { drawOnChartArea: true } 
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (context.datasetIndex === 0) { 
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                        } else {
                                            label += context.parsed.y;
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // --- 2. SCRIPT LIVE SEARCH & PAGINATION ---
    $(document).ready(function() {
        
        function fetch_data(url) {
            let loading = $('#search-loading');
            loading.removeClass('hidden');

            $.ajax({
                url: url,
                method: "GET",
                success: function(response) {
                    $('#table-body').html(response);
                    loading.addClass('hidden');
                },
                error: function() {
                    loading.addClass('hidden');
                    console.error('Gagal memuat data.');
                }
            });
        }

        let timeout = null;
        $('#live-search').on('keyup', function() {
            clearTimeout(timeout);
            let query = $(this).val();
            let base_url = "{{ route('admin.analytics') }}" + "?q=" + query;

            timeout = setTimeout(function() {
                fetch_data(base_url);
            }, 500);
        });

        $(document).on('click', '.pagination a', function(event) {
            event.preventDefault(); 
            let url = $(this).attr('href'); 
            if(url) {
                fetch_data(url);
            }
        });
    });
</script>
@endpush