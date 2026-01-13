@extends('admin.layout')

@section('content')
<div class="max-w-6xl mx-auto">
    
    {{-- HEADER (LAYOUT DIPERBAIKI) --}}
    <div class="mb-8">
        {{-- 1. Tombol Kembali ditaruh di atas agar tidak tabrakan --}}
        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 font-bold text-sm transition inline-flex items-center gap-2 mb-3">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        {{-- 2. Judul dan Badge Status --}}
        <div class="flex flex-wrap items-center gap-3 mb-2">
            <h1 class="text-2xl font-bold text-slate-800">Template Email Reminder</h1>
            
            @if($batch->is_active)
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200 inline-flex items-center gap-1">
                    <i class="fas fa-check-circle"></i> Sedang Aktif
                </span>
            @else
                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold border border-gray-200 inline-flex items-center gap-1">
                    <i class="fas fa-archive"></i> Tidak Aktif
                </span>
            @endif
        </div>

        {{-- 3. Deskripsi --}}
        <p class="text-sm text-gray-500">Atur pesan otomatis yang akan dikirim H-1 sebelum PO ditutup.</p>
    </div>

    {{-- ALERT SUKSES --}}
    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-bold border border-green-200 flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI: FORM EDIT EMAIL --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative flex flex-col h-full">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-envelope-open-text text-blue-600"></i> Isi Pesan Email
                    </h3>
                </div>
                
                <form action="{{ route('admin.batch.update_mail') }}" method="POST" class="p-6 flex-1 flex flex-col">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                    
                    <div class="mb-4 flex-1">
                        <textarea name="mail_message" rows="15" class="w-full border border-gray-300 rounded-xl p-4 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-mono leading-relaxed shadow-inner bg-gray-50 focus:bg-white transition resize-none" required placeholder="Tulis pesan di sini...">{{ $batch->mail_message }}</textarea>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-auto pt-2">
                        <p class="text-xs text-gray-400 italic">
                            <i class="fas fa-clock"></i> Terakhir diupdate: {{ $batch->updated_at->diffForHumans() }}
                        </p>
                        <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- KOLOM KANAN: STATUS & KODE --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- STATUS PENGIRIMAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-gray-700 mb-4 text-sm uppercase tracking-wide border-b pb-2">Status Pengiriman</h3>
                
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $batch->is_reminder_sent ? 'bg-green-100 text-green-600' : 'bg-orange-100 text-orange-500' }}">
                        <i class="fas {{ $batch->is_reminder_sent ? 'fa-check-double' : 'fa-clock' }} text-xl"></i>
                    </div>
                    <div>
                        @if($batch->is_reminder_sent)
                            <p class="font-bold text-slate-800">Sudah Terkirim</p>
                            <p class="text-xs text-gray-500">Email sudah disebar ke pemesan.</p>
                        @else
                            <p class="font-bold text-slate-800">Menunggu Jadwal</p>
                            <p class="text-xs text-gray-500">Belum dikirim.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600 border border-gray-100 space-y-1">
                    <div class="flex justify-between">
                        <span>Jadwal Kirim:</span>
                        <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($batch->close_date)->subDay()->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pukul:</span>
                        <span class="font-bold text-slate-700">08:00 WIB (Estimasi)</span>
                    </div>
                </div>
            </div>

            {{-- KODE OTOMATIS --}}
            <div class="bg-slate-800 text-white rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-white mb-4 text-sm flex items-center gap-2 border-b border-slate-600 pb-2">
                    <i class="fas fa-code text-blue-400"></i> Kode Otomatis
                </h3>
                <p class="text-xs text-slate-400 mb-4 leading-relaxed">Copy kode di bawah ini ke dalam pesan. Sistem akan menggantinya dengan data asli pelanggan.</p>
                
                <div class="space-y-2">
                    <div class="group flex items-center justify-between bg-slate-700 p-2.5 rounded-lg border border-slate-600 hover:border-blue-500 transition cursor-pointer" onclick="navigator.clipboard.writeText('{nama_pemesan}')">
                        <div>
                            <code class="text-yellow-400 font-mono text-xs block mb-0.5">{nama_pemesan}</code>
                            <span class="text-[10px] text-slate-400">Nama Customer</span>
                        </div>
                        <i class="fas fa-copy text-slate-500 group-hover:text-white"></i>
                    </div>

                    <div class="group flex items-center justify-between bg-slate-700 p-2.5 rounded-lg border border-slate-600 hover:border-blue-500 transition cursor-pointer" onclick="navigator.clipboard.writeText('{nama_kegiatan}')">
                        <div>
                            <code class="text-yellow-400 font-mono text-xs block mb-0.5">{nama_kegiatan}</code>
                            <span class="text-[10px] text-slate-400">Nama PO (Cth: PO Januari)</span>
                        </div>
                        <i class="fas fa-copy text-slate-500 group-hover:text-white"></i>
                    </div>

                    <div class="group flex items-center justify-between bg-slate-700 p-2.5 rounded-lg border border-slate-600 hover:border-blue-500 transition cursor-pointer" onclick="navigator.clipboard.writeText('{detail_pesanan}')">
                        <div>
                            <code class="text-yellow-400 font-mono text-xs block mb-0.5">{detail_pesanan}</code>
                            <span class="text-[10px] text-slate-400">List Barang & Total Harga</span>
                        </div>
                        <i class="fas fa-copy text-slate-500 group-hover:text-white"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection