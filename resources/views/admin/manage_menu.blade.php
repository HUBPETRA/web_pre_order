@extends('admin.layout')

@section('content')
<div class="max-w-5xl mx-auto">
    
    {{-- HEADER & NAVIGASI (DIPERBAIKI) --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 text-sm font-bold inline-flex items-center gap-2 mb-2 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800">Kelola Menu: {{ $batch->name }}</h1>
                
                {{-- BADGE STATUS (Posisi diperbaiki agar lebih rapi) --}}
                @if($batch->is_active)
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200 inline-flex items-center gap-1">
                        <i class="fas fa-check-circle"></i> Sedang Aktif
                    </span>
                @else
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold border border-gray-200 inline-flex items-center gap-1">
                        <i class="fas fa-archive"></i> Draft / Tidak Aktif
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ALERT ERROR --}}
    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 border border-red-200 rounded-xl mb-6 text-sm shadow-sm">
            <p class="font-bold flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-lg"></i> Gagal Menambah Menu:
            </p>
            <ul class="list-disc ml-8 mt-1 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM TAMBAH MENU --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
        <h3 class="font-bold text-gray-700 mb-4 text-lg flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-600"></i> Tambah Menu Baru
        </h3>
        
        <form action="{{ route('admin.batch.add_product') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-5">
            @csrf
            <input type="hidden" name="batch_id" value="{{ $batch->id }}">
            
            <div class="md:col-span-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Menu</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Cth: Ayam Bakar Madu" required>
            </div>
            
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="15000" required>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stok Total</label>
                <input type="number" name="stock" value="{{ old('stock') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="50" required>
            </div>

            <div class="md:col-span-12">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Singkat</label>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Cth: Ayam bakar dengan bumbu madu spesial + lalapan." required>{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-12">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Foto Menu (JPG/PNG)</label>
                <input type="file" name="image" class="block w-full text-sm text-slate-500
                    file:mr-4 file:py-2.5 file:px-4
                    file:rounded-full file:border-0
                    file:text-xs file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                    cursor-pointer
                " required>
            </div>
            
            <div class="md:col-span-12 flex justify-end mt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition transform active:scale-95">
                    <i class="fas fa-save mr-2"></i> Simpan Menu
                </button>
            </div>
        </form>
    </div>

    {{-- TABEL MENU (DIPERBAIKI UNTUK MOBILE) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
        <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700 text-sm md:text-base">Daftar Menu</h3>
            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $batch->products->count() }} Item</span>
        </div>

        {{-- CONTAINER SCROLL HORIZONTAL --}}
        <div class="overflow-x-auto w-full custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="p-4 w-16">Foto</th>
                        <th class="p-4 min-w-[200px]">Nama & Deskripsi</th>
                        <th class="p-4">Harga</th>
                        <th class="p-4 text-center">Sisa Stok</th> 
                        <th class="p-4 text-center">Status</th>
                        {{-- KOLOM AKSI STICKY DI KANAN --}}
                        <th class="p-4 text-right sticky right-0 bg-gray-50 z-10 shadow-[-5px_0px_10px_rgba(0,0,0,0.02)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($batch->products as $product)
                    <tr class="hover:bg-blue-50/50 transition group">
                        <td class="p-4 align-top">
                            @if($product->image)
                                <img src="{{ asset('uploads/products/' . $product->image) }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200 shadow-sm shrink-0">
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 shrink-0 border border-gray-200">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            @endif
                        </td>
                        <td class="p-4 align-top whitespace-normal min-w-[200px] max-w-xs">
                            <div class="font-bold text-slate-800 text-base mb-1">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500 leading-relaxed line-clamp-2">{!! nl2br(e($product->description)) !!}</div>
                        </td>
                        <td class="p-4 align-top font-mono text-slate-600">Rp {{ number_format($product->pivot->price) }}</td>
                        
                        <td class="p-4 text-center align-top">
                            @php
                                $sisa = $product->pivot->stock - $product->pivot->sold;
                                $persen = $product->pivot->stock > 0 ? ($sisa / $product->pivot->stock) * 100 : 0;
                            @endphp
                            
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-lg {{ $sisa == 0 ? 'text-red-500' : 'text-slate-700' }}">
                                    {{ $sisa }}
                                </span>
                                <span class="text-[10px] text-gray-400 uppercase tracking-wider">Porsi</span>
                                
                                {{-- Progress Bar Kecil --}}
                                <div class="w-16 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full {{ $sisa < 5 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="p-4 text-center align-top">
                            @if($product->pivot->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-600 rounded-full mr-1.5"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 bg-red-600 rounded-full mr-1.5"></span> Mati
                                </span>
                            @endif
                        </td>
                        
                        {{-- KOLOM AKSI STICKY (Agar tidak hilang saat scroll di HP) --}}
                        <td class="p-4 text-right align-top sticky right-0 bg-white group-hover:bg-blue-50/50 z-10 border-l border-gray-100 shadow-[-5px_0px_10px_rgba(0,0,0,0.02)]">
                            <div class="flex flex-col gap-2 items-end justify-center h-full">
                                {{-- Tombol Edit --}}
                                <button type="button" 
                                    onclick="openEditModal(this)"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->pivot->price }}"
                                    data-stock="{{ $product->pivot->stock }}"
                                    data-description="{{ $product->description }}"
                                    data-image-url="{{ $product->image ? asset('uploads/products/' . $product->image) : '' }}"
                                    class="text-blue-600 hover:text-blue-800 font-bold text-xs flex items-center gap-1 px-2 py-1 rounded hover:bg-blue-100 transition">
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                {{-- Tombol Toggle --}}
                                <form action="{{ route('admin.product.toggle', $product->id) }}" method="POST">
                                    @csrf
                                    <button class="text-xs font-bold flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100 transition {{ $product->pivot->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}">
                                        @if($product->pivot->is_active)
                                            <i class="fas fa-power-off"></i> Matikan
                                        @else
                                            <i class="fas fa-check"></i> Hidupkan
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-400">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-hamburger text-4xl mb-3 text-gray-200"></i>
                                <p>Belum ada menu yang ditambahkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 text-right">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-slate-500/30 transition transform hover:-translate-y-1">
            <i class="fas fa-check-circle mr-2"></i> Selesai & Kembali
        </a>
    </div>
</div>

{{-- MODAL EDIT PRODUK --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0 flex flex-col max-h-[90vh]" id="editModalContent">
        
        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50 shrink-0">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fas fa-edit text-blue-600"></i> Edit Menu
            </h3>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        {{-- Form Scrollable --}}
        <div class="overflow-y-auto p-6">
            <form action="{{ route('admin.product.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                <input type="hidden" name="product_id" id="edit_product_id">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Menu</label>
                    <input type="text" name="name" id="edit_name" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga (Rp)</label>
                        <input type="number" name="price" id="edit_price" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stok Total</label>
                        <input type="number" name="stock" id="edit_stock" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        <p class="text-[10px] text-gray-400 mt-1">*Masukkan kapasitas total.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition" required></textarea>
                </div>

                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Foto Menu</label>
                    
                    <div class="flex items-start gap-4">
                        {{-- Preview Foto Lama --}}
                        <div id="current_image_container" class="hidden shrink-0">
                            <img id="current_image_preview" src="" class="h-16 w-16 rounded-lg object-cover border border-blue-200 shadow-sm bg-white">
                        </div>

                        <div class="flex-1">
                            <input type="file" name="image" class="block w-full text-xs text-slate-500
                                file:mr-2 file:py-2 file:px-3
                                file:rounded-md file:border-0
                                file:text-xs file:font-semibold
                                file:bg-white file:text-blue-700
                                hover:file:bg-blue-100
                                cursor-pointer
                            ">
                            <p class="text-[10px] text-gray-400 mt-1">Upload foto baru jika ingin mengganti.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t mt-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200 transition text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow transition text-sm flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(button) {
        // Ambil data aman
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const price = button.getAttribute('data-price');
        const stock = button.getAttribute('data-stock');
        const desc = button.getAttribute('data-description');
        const imageUrl = button.getAttribute('data-image-url');

        // Isi Form
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_description').value = desc;

        // Preview Foto
        const imgContainer = document.getElementById('current_image_container');
        const imgPreview = document.getElementById('current_image_preview');

        if (imageUrl) {
            imgPreview.src = imageUrl;
            imgContainer.classList.remove('hidden');
        } else {
            imgContainer.classList.add('hidden');
        }

        // Buka Modal
        const modal = document.getElementById('editModal');
        const content = document.getElementById('editModalContent');
        modal.classList.remove('hidden');
        
        // Animasi
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        const content = document.getElementById('editModalContent');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endsection