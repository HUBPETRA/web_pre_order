@extends('admin.layout')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Analitik & Riwayat</h1>
            <p class="text-gray-500">Pantau performa penjualan dan arsip kegiatan lama.</p>
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
        <h3 class="font-bold text-lg mb-4 text-slate-700">Tren Pesanan Lunas</h3>
        <div class="relative h-64 w-full">
            <canvas id="salesChart"></canvas>
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
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-gray-100" id="table-body">
                @include('admin.partials.batch_table', ['batches' => $batches])
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // 1. Script Grafik
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Pesanan Lunas',
                data: {!! json_encode($chartData) !!},
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(37, 99, 235, 1)',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } },
            plugins: { legend: { display: false } }
        }
    });

    // 2. SCRIPT LIVE SEARCH (AJAX)
    $(document).ready(function() {
        let timeout = null;
        
        $('#live-search').on('keyup', function() {
            clearTimeout(timeout); // Reset timer saat user mengetik
            
            let query = $(this).val();
            let loading = $('#search-loading');
            
            loading.removeClass('hidden'); // Tampilkan loading
            
            // Tunggu 500ms setelah user berhenti mengetik baru kirim request
            timeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.analytics') }}",
                    method: "GET",
                    data: { q: query },
                    success: function(response) {
                        // Ganti isi tabel dengan hasil baru
                        $('#table-body').html(response);
                        loading.addClass('hidden');
                    },
                    error: function() {
                        loading.addClass('hidden');
                        console.error('Error fetching search results');
                    }
                });
            }, 500);
        });
    });
</script>
@endpush