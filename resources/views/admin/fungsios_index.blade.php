@extends('admin.layout')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen SDM & Kuota</h1>
            <p class="text-sm text-gray-500">Kelola daftar anggota dan standar target penjualan divisi.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 font-bold text-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="bg-slate-800 text-white rounded-xl p-6 sticky top-6 shadow-xl">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-600 pb-4">
                    <div class="bg-blue-500/20 p-2 rounded-lg text-blue-400">
                        <i class="fas fa-sliders-h text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Rumus Default</h3>
                        <p class="text-[10px] text-slate-400">Target otomatis saat buat PO baru.</p>
                    </div>
                </div>
                
                <form action="{{ route('admin.division.defaults') }}" method="POST">
                    @csrf
                    @php $divs = ['Acara', 'Humas', 'Perlengkapan', 'Konsumsi', 'Keamanan', 'Lainnya']; @endphp
                    
                    <div class="space-y-3">
                        @foreach($divs as $div)
                        @php 
                            $val = $defaults->where('division_name', $div)->first()->default_quota ?? 0; 
                        @endphp
                        <div class="flex justify-between items-center bg-slate-700/50 p-3 rounded-lg border border-slate-600 hover:bg-slate-700 transition">
                            <label class="text-sm font-bold text-slate-200">{{ $div }}</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="defaults[{{ $div }}]" value="{{ $val }}" 
                                       class="w-16 bg-slate-900 text-white border border-slate-500 rounded px-2 py-1 text-center text-sm font-bold focus:ring-1 focus:ring-blue-400 outline-none">
                                <span class="text-[10px] text-slate-400">porsi</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white mt-6 py-3 rounded-lg font-bold text-sm transition shadow-lg flex justify-center items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Settingan
                    </button>
                </form>

                <div class="mt-6 bg-slate-700/30 p-3 rounded-lg border border-slate-600">
                    <p class="text-[10px] text-slate-400 leading-relaxed">
                        <i class="fas fa-info-circle text-blue-400 mr-1"></i> 
                        Perubahan di sini <b>tidak mempengaruhi</b> PO yang sedang berjalan. Ini hanya template untuk PO masa depan.
                    </p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-plus text-blue-600"></i> Tambah Anggota Baru
                </h3>
                <form action="{{ route('admin.fungsios.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <input type="text" name="name" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-sm" placeholder="Nama Lengkap" required>
                    <input type="email" name="email" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-sm" placeholder="Email" required>
                    <select name="division" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none bg-white text-sm" required>
                        <option value="" disabled selected>Divisi</option>
                        @foreach($divs as $div) <option value="{{ $div }}">{{ $div }}</option> @endforeach
                    </select>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow-md transition text-sm">
                        Simpan
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Daftar Anggota Aktif</h3>
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-bold">{{ count($fungsios) }} Orang</span>
                </div>
                
                <div class="overflow-x-auto max-h-[500px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs sticky top-0 z-10">
                            <tr>
                                <th class="p-4">Nama & Email</th>
                                <th class="p-4">Divisi</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($fungsios as $f)
                            <tr class="hover:bg-blue-50 transition">
                                <td class="p-4">
                                    <div class="font-bold text-slate-700">{{ $f->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $f->email }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px] font-bold uppercase border border-slate-200">
                                        {{ $f->division }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    @if($f->is_active)
                                        <span class="text-green-500 text-xs font-bold"><i class="fas fa-check-circle"></i> Aktif</span>
                                    @else
                                        <span class="text-gray-400 text-xs"><i class="fas fa-times-circle"></i> Nonaktif</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <form action="{{ route('admin.fungsios.toggle', $f->id) }}" method="POST">
                                        @csrf
                                        <button class="text-xs font-bold underline {{ $f->is_active ? 'text-red-500' : 'text-green-600' }}">
                                            {{ $f->is_active ? 'Matikan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-8 text-center text-gray-400">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection