@extends('admin.layout')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-lg p-6 bg-white rounded-xl shadow-sm border border-gray-100">
        
        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 text-blue-200">
            <i class="fas fa-folder-open text-4xl"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Belum Ada Kegiatan PO Aktif</h1>
        <p class="text-slate-500 mb-8 text-sm leading-relaxed">
            Saat ini tidak ada Pre-Order yang sedang berjalan. <br>
            Silakan buat kegiatan baru atau lihat data lama.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.batch.store') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle"></i> Buka Pre-Order Baru
            </a>
            
            <a href="{{ route('admin.analytics') }}" class="w-full bg-gray-50 hover:bg-gray-100 text-slate-600 font-bold py-3 px-6 rounded-xl border border-gray-200 transition flex items-center justify-center gap-2">
                <i class="fas fa-history"></i> Lihat Riwayat / Arsip
            </a>
        </div>

    </div>
</div>
@endsection