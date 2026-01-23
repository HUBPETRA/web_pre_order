@extends('admin.layout')

@section('content')
{{-- DEFINISI DIVISI (HARDCODED) --}}
{{-- Ubah nama-nama divisi di sini, otomatis akan berubah di semua form dan tabel di bawah --}}
@php 
    $divs = ['BPH', 'METER', 'LITER', 'FOTO', 'ILLUS', 'RND', 'DC', 'IT', 'FUNDING']; 
@endphp

<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Fungsio & Kuota</h1>
            <p class="text-sm text-gray-500">Kelola daftar fungsio dan standar target penjualan divisi.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 font-bold text-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI: RUMUS DEFAULT --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-800 text-white rounded-xl p-6 sticky top-6 shadow-xl">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-600 pb-4">
                    <div class="bg-blue-500/20 p-2 rounded-lg text-blue-400">
                        <i class="fas fa-sliders-h text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Kuota Default</h3>
                        <p class="text-[10px] text-slate-400">Berubah otomatis saat buat PO baru.</p>
                    </div>
                </div>
                
                <form action="{{ route('admin.division.defaults') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-3">
                        @foreach($divs as $div)
                        @php 
                            // Mengambil nilai default dari database, jika tidak ada set 0
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
                        Perubahan di sini <b>tidak mempengaruhi</b> PO yang sedang berjalan.
                    </p>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: LIST ANGGOTA --}}
        <div class="lg:col-span-2 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            {{-- FORM TAMBAH ANGGOTA --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-plus text-blue-600"></i> Tambah Anggota Baru
                </h3>
                <form action="{{ route('admin.fungsios.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                    @csrf
                    
                    {{-- Input Nama --}}
                    <div class="md:col-span-1">
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="w-full border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-sm" 
                               placeholder="Nama Lengkap" required>
                        @error('name')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Email --}}
                    <div class="md:col-span-1">
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none text-sm" 
                               placeholder="Email" required>
                        @error('email')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Select Divisi --}}
                    <div class="md:col-span-1">
                        <select name="division" class="w-full border @error('division') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none bg-white text-sm" required>
                            <option value="" disabled selected>Divisi</option>
                            @foreach($divs as $div) 
                                <option value="{{ $div }}" {{ old('division') == $div ? 'selected' : '' }}>{{ $div }}</option> 
                            @endforeach
                        </select>
                        @error('division')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="md:col-span-1">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow-md transition text-sm">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>

            {{-- TABEL ANGGOTA --}}
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
                                    <div class="flex justify-end items-center gap-3">
                                        {{-- TOMBOL EDIT (MEMICU MODAL) --}}
                                        <button onclick="openEditModal({{ $f->id }}, '{{ $f->name }}', '{{ $f->email }}', '{{ $f->division }}')" 
                                                class="text-blue-500 hover:text-blue-700 transition" title="Edit Data">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>

                                        {{-- TOMBOL TOGGLE STATUS --}}
                                        <form action="{{ route('admin.fungsios.toggle', $f->id) }}" method="POST">
                                            @csrf
                                            <button class="text-xs font-bold underline {{ $f->is_active ? 'text-red-500' : 'text-green-600' }}">
                                                {{ $f->is_active ? 'Matikan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
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

{{-- MODAL EDIT ANGGOTA --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100 opacity-100">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-lg text-slate-800">Edit Data Anggota</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT') {{-- Method Spoofing untuk Update --}}
            
            {{-- Input Nama Edit --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Input Email Edit --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                <input type="email" id="edit_email" name="email" required
                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Select Divisi Edit --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Divisi</label>
                <select id="edit_division" name="division" required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                    @foreach($divs as $div)
                        <option value="{{ $div }}">{{ $div }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t mt-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT UNTUK MODAL EDIT --}}
<script>
    function openEditModal(id, name, email, division) {
        // 1. Isi nilai input form dengan data yang diklik
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_division').value = division;
        
        // 2. Update Action URL Form secara dinamis
        // Asumsi route update adalah admin/fungsios/{id}
        // Kita gunakan placeholder '000' lalu replace dengan ID asli
        let url = "{{ route('admin.fungsios.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editForm').action = url;

        // 3. Tampilkan Modal
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>

@endsection