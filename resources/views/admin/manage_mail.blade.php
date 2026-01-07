@extends('admin.layout')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800">Template Email Reminder</h1>
                @if($batch->is_active)
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold border border-green-200">
                        Sedang Aktif
                    </span>
                @else
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold border border-gray-200">
                        Tidak Aktif
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500">Atur pesan otomatis yang akan dikirim H-1 sebelum PO ditutup.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 font-bold text-sm transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 text-sm font-bold border border-green-200 flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Isi Pesan Email</h3>
                </div>
                
                <form action="{{ route('admin.batch.update_mail') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                    
                    <div class="mb-4">
                        <textarea name="mail_message" rows="12" class="w-full border border-gray-300 rounded-lg p-4 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-mono leading-relaxed shadow-inner bg-gray-50 focus:bg-white transition" required placeholder="Tulis pesan di sini...">{{ $batch->mail_message }}</textarea>
                    </div>

                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-400 italic">Terakhir diupdate: {{ $batch->updated_at->diffForHumans() }}</p>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition transform hover:-translate-y-1 flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-gray-700 mb-4 text-sm uppercase tracking-wide">Status Pengiriman</h3>
                
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $batch->is_reminder_sent ? 'bg-green-100 text-green-600' : 'bg-orange-100 text-orange-500' }}">
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

                <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600 border border-gray-100">
                    <div class="flex justify-between mb-1">
                        <span>Jadwal Kirim:</span>
                        <span class="font-bold">{{ \Carbon\Carbon::parse($batch->close_date)->subDay()->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pukul:</span>
                        <span class="font-bold">08:00 WIB (Estimasi)</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 text-white rounded-xl shadow-lg p-6">
                <h3 class="font-bold text-white mb-4 text-sm flex items-center gap-2">
                    <i class="fas fa-code text-blue-400"></i> Kode Otomatis
                </h3>
                <p class="text-xs text-slate-400 mb-4">Gunakan kode di bawah ini di dalam pesan Anda. Sistem akan menggantinya secara otomatis.</p>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between bg-slate-700 p-2 rounded border border-slate-600">
                        <code class="text-yellow-400 font-mono text-xs">{nama_pemesan}</code>
                        <span class="text-[10px] text-slate-300">Nama Customer</span>
                    </div>
                    <div class="flex items-center justify-between bg-slate-700 p-2 rounded border border-slate-600">
                        <code class="text-yellow-400 font-mono text-xs">{nama_kegiatan}</code>
                        <span class="text-[10px] text-slate-300">Nama PO</span>
                    </div>
                    <div class="flex items-center justify-between bg-slate-700 p-2 rounded border border-slate-600">
                        <code class="text-yellow-400 font-mono text-xs">{detail_pesanan}</code>
                        <span class="text-[10px] text-slate-300">List Barang</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection