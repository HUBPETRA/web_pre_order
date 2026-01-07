@extends('admin.layout')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Target PO: {{ $batch->name }}</h1>
            <p class="text-sm text-gray-500">Pantau realisasi penjualan dan sesuaikan target personal jika diperlukan.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold animate-pulse">
                ● Sedang Aktif
            </span>
            <a href="{{ route('admin.dashboard') }}" class="bg-white border border-gray-300 text-gray-600 hover:text-blue-600 px-4 py-2 rounded-lg font-bold text-sm transition shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 text-sm font-bold border border-green-200 flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.batch.quotas.update', $batch->id) }}" method="POST">
            @csrf
            
            @foreach($quotasByDivision as $division => $quotas)
            <div class="mb-8 border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                <div class="bg-gray-50 p-3 border-b border-gray-100 flex justify-between items-center">
                    <h4 class="font-bold text-slate-700 uppercase text-xs tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> {{ $division }}
                    </h4>
                    <button type="button" onclick="bulkUpdate('{{ $division }}')" class="text-[10px] bg-white border border-gray-300 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 px-3 py-1.5 rounded-lg transition font-semibold shadow-sm">
                        <i class="fas fa-magic mr-1"></i> Samakan Target Divisi
                    </button>
                </div>
                
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white">
                    @foreach($quotas as $q)
                    <div class="border border-gray-200 p-3 rounded-lg hover:shadow-md transition bg-white relative overflow-hidden group">
                        <div class="flex items-start justify-between mb-3 relative z-10">
                            <div>
                                <p class="font-bold text-sm text-slate-800">{{ $q->fungsio->name }}</p>
                                <p class="text-[10px] text-gray-400 truncate max-w-[140px]">{{ $q->fungsio->email }}</p>
                            </div>
                            <div class="text-right">
                                <label class="block text-[9px] text-gray-400 mb-1 font-bold uppercase">Target</label>
                                <input type="number" 
                                       name="quotas[{{ $q->fungsio_id }}]" 
                                       value="{{ $q->target_qty }}" 
                                       class="input-{{Str::slug($division)}} w-16 border border-gray-300 rounded px-1 py-1 text-center font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 outline-none text-sm shadow-inner bg-gray-50 focus:bg-white transition">
                            </div>
                        </div>

                        @php
                            $percent = $q->target_qty > 0 ? ($q->achieved_qty / $q->target_qty) * 100 : 0;
                            $sisa = $q->remaining_qty;
                            
                            if ($sisa < 0) { 
                                $barColor = 'bg-red-500'; $textColor = 'text-red-600'; $statusText = "Over " . abs($sisa);
                            } elseif ($sisa == 0 && $q->target_qty > 0) { 
                                $barColor = 'bg-green-500'; $textColor = 'text-green-600'; $statusText = "Lunas";
                            } else { 
                                $barColor = 'bg-blue-500'; $textColor = 'text-blue-600'; $statusText = "Sisa $sisa";
                            }
                        @endphp

                        <div class="relative z-10">
                            <div class="flex justify-between text-[10px] font-bold mb-1">
                                <span class="text-gray-500">{{ $q->achieved_qty }} Terjual</span>
                                <span class="{{ $textColor }}">{{ $statusText }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $percent) }}%"></div>
                            </div>
                        </div>

                        @if($sisa < 0)
                            <div class="absolute inset-0 bg-red-50 opacity-20 pointer-events-none"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            @if($quotasByDivision->isEmpty())
                <div class="text-center p-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <p class="text-gray-500">Belum ada data Fungsio di batch ini.</p>
                </div>
            @endif

            <div class="sticky bottom-6 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl shadow-2xl transition transform hover:-translate-y-1 border-b-4 border-green-800 flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan Batch Ini
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function bulkUpdate(division) {
    let slug = division.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
    let newVal = prompt("Masukkan nilai target baru untuk seluruh anggota divisi " + division + ":");
    
    if(newVal !== null && newVal.trim() !== "") {
        let inputs = document.querySelectorAll('.input-' + slug);
        inputs.forEach(input => {
            input.value = newVal;
            input.style.backgroundColor = '#ecfdf5';
            input.style.borderColor = '#10b981';
        });
    }
}
</script>
@endsection