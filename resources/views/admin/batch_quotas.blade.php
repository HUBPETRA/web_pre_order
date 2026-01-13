@extends('admin.layout')

@section('content')
<div class="max-w-6xl mx-auto pb-24"> <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Target PO: {{ $batch->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau realisasi penjualan dan sesuaikan target personal jika diperlukan.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Sedang Aktif
            </span>
            <a href="{{ route('admin.dashboard') }}" class="bg-white border border-gray-300 text-gray-600 hover:text-blue-600 px-4 py-2 rounded-lg font-bold text-sm transition shadow-sm hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 text-sm font-bold border border-green-200 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-check-circle text-xl"></i> 
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.batch.quotas.update', $batch->id) }}" method="POST">
            @csrf
            
            @forelse($quotasByDivision as $division => $quotas)
            <div class="mb-8 border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="bg-gray-50 p-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <h4 class="font-bold text-slate-700 uppercase text-xs tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> {{ $division }}
                    </h4>
                    <button type="button" onclick="bulkUpdate('{{ $division }}')" class="w-full sm:w-auto text-xs bg-white border border-gray-300 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 px-3 py-2 rounded-lg transition font-semibold shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-magic"></i> Samakan Target Divisi
                    </button>
                </div>
                
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-white">
                    @foreach($quotas as $q)
                    <div class="border border-gray-200 p-4 rounded-xl hover:border-blue-300 transition-colors bg-white relative overflow-hidden group">
                        
                        <div class="flex items-start justify-between mb-4 relative z-10">
                            <div class="overflow-hidden pr-2">
                                <p class="font-bold text-sm text-slate-800 truncate">{{ $q->fungsio->name }}</p>
                                <p class="text-[11px] text-gray-400 truncate" title="{{ $q->fungsio->email }}">{{ $q->fungsio->email }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <label class="block text-[10px] text-gray-400 mb-1 font-bold uppercase tracking-wide">Target</label>
                                <input type="number" 
                                       name="quotas[{{ $q->fungsio_id }}]" 
                                       value="{{ $q->target_qty }}" 
                                       class="input-{{Str::slug($division)}} w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-center font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm bg-gray-50 focus:bg-white transition"
                                       min="0">
                            </div>
                        </div>

                        @php
                            $percent = $q->target_qty > 0 ? ($q->achieved_qty / $q->target_qty) * 100 : 0;
                            $sisa = $q->remaining_qty;
                            
                            if ($sisa < 0) { 
                                // Over Target (Bagus/Bonus) -> Warna Ungu/Emas? Atau Tetap Hijau Tua?
                                // Biasanya Over target itu bagus. Mari kita pakai Ungu untuk prestasi.
                                $barColor = 'bg-purple-500'; 
                                $textColor = 'text-purple-600'; 
                                $statusText = "Over " . abs($sisa);
                                $bgColor = 'bg-purple-50';
                            } elseif ($sisa == 0 && $q->target_qty > 0) { 
                                // Pas Target -> Hijau
                                $barColor = 'bg-green-500'; 
                                $textColor = 'text-green-600'; 
                                $statusText = "Lunas";
                                $bgColor = 'bg-green-50';
                            } else { 
                                // Belum Target -> Biru (Normal) atau Kuning/Merah (Warning)
                                // Kita pakai Biru standard, merah jika progress < 50%? Sederhana saja: Biru.
                                $barColor = 'bg-blue-500'; 
                                $textColor = 'text-blue-600'; 
                                $statusText = "Kurang $sisa";
                                $bgColor = 'bg-gray-50'; // Default background
                            }

                            // Jika target 0, tapi ada penjualan -> Over
                            if ($q->target_qty == 0 && $q->achieved_qty > 0) {
                                 $barColor = 'bg-purple-500'; 
                                 $textColor = 'text-purple-600'; 
                                 $statusText = "Bonus " . $q->achieved_qty;
                                 $bgColor = 'bg-purple-50';
                            }
                        @endphp

                        <div class="relative z-10">
                            <div class="flex justify-between text-[11px] font-bold mb-1.5">
                                <span class="text-gray-500">{{ $q->achieved_qty }} Terjual</span>
                                <span class="{{ $textColor }}">{{ $statusText }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-1000 ease-out" style="width: {{ min(100, $percent) }}%"></div>
                            </div>
                        </div>

                        @if($sisa <= 0 && $q->target_qty > 0)
                            <div class="absolute inset-0 {{ $bgColor }} opacity-30 pointer-events-none transition-opacity group-hover:opacity-40"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
                <div class="text-center p-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-4">
                        <i class="fas fa-users-slash text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum ada data Fungsio</h3>
                    <p class="text-gray-500 mt-1">Pastikan Anda sudah menambahkan data fungsio dan divisi di menu Master Data.</p>
                </div>
            @endforelse

            <div class="fixed bottom-6 right-6 z-40">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-full shadow-2xl transition-all transform hover:-translate-y-1 hover:scale-105 flex items-center gap-3 border-4 border-white ring-4 ring-blue-100">
                    <i class="fas fa-save text-xl"></i> 
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function bulkUpdate(division) {
    // Generate slug sederhana untuk selector class
    let slug = division.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
    let inputs = document.querySelectorAll('.input-' + slug);
    
    if (inputs.length === 0) {
        alert("Tidak ada anggota di divisi ini.");
        return;
    }

    let newVal = prompt("Masukkan nilai target baru untuk " + inputs.length + " anggota divisi " + division + ":");
    
    if(newVal !== null && newVal.trim() !== "") {
        // Validasi input angka
        if (isNaN(newVal) || newVal < 0) {
            alert("Harap masukkan angka yang valid (positif).");
            return;
        }

        inputs.forEach(input => {
            input.value = newVal;
            // Visual feedback
            input.classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-700');
            setTimeout(() => {
                input.classList.remove('bg-yellow-50', 'border-yellow-400', 'text-yellow-700');
            }, 1000);
        });
    }
}
</script>

<style>
/* Animasi Fade In */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}
.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out;
}
</style>
@endsection