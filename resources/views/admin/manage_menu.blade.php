@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 text-sm font-bold">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-bold text-slate-800 mt-2">Kelola Menu: {{ $batch->name }}</h1>
        </div>
        
        @if($batch->is_active)
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">Sedang Aktif</span>
        @else
            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">Draft / Tidak Aktif</span>
        @endif
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 border border-red-200 rounded-lg mb-6 text-sm">
            <p class="font-bold flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> Gagal Menambah Menu:
            </p>
            <ul class="list-disc ml-6 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
        <h3 class="font-bold text-gray-700 mb-4 text-lg">Tambah Menu Baru</h3>
        
        <form action="{{ route('admin.batch.add_product') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            @csrf
            <input type="hidden" name="batch_id" value="{{ $batch->id }}">
            
            <div class="md:col-span-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Menu</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Cth: Ayam Bakar Madu" required>
            </div>
            
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="15000" required>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stok Total</label>
                <input type="number" name="stock" value="{{ old('stock') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="50" required>
            </div>

            <div class="md:col-span-12">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Singkat</label>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Cth: Ayam bakar dengan bumbu madu spesial + lalapan." required>{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-12">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Foto Menu (JPG/PNG)</label>
                <input type="file" name="image" class="block w-full text-sm text-slate-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                " required>
            </div>
            
            <div class="md:col-span-12 flex justify-end mt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                    <i class="fas fa-plus mr-2"></i> Tambah Menu
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b text-gray-500 uppercase text-xs">
                <tr>
                    <th class="p-4">Foto</th>
                    <th class="p-4">Nama & Deskripsi</th>
                    <th class="p-4">Harga</th>
                    <th class="p-4 text-center">Sisa Stok</th> <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($batch->products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="p-4 w-20">
                        @if($product->image)
                            <img src="{{ asset('uploads/products/' . $product->image) }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                        @else
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </td>
                    <td class="p-4">
                        <div class="font-bold text-slate-700">{{ $product->name }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ $product->description }}</div>
                    </td>
                    <td class="p-4">Rp {{ number_format($product->pivot->price) }}</td>
                    
                    <td class="p-4 text-center">
                        @php
                            // Hitung Sisa = Stok Awal - Terjual
                            $sisa = $product->pivot->stock - $product->pivot->sold;
                        @endphp
                        
                        <span class="font-bold text-lg {{ $sisa == 0 ? 'text-red-500' : 'text-gray-700' }}">
                            {{ $sisa }}
                        </span>
                        <span class="text-xs text-gray-400 block">Porsi</span>
                    </td>

                    <td class="p-4 text-center">
                        @if($product->pivot->is_active)
                            <span class="text-green-600 font-bold text-xs bg-green-100 px-2 py-1 rounded">Aktif</span>
                        @else
                            <span class="text-red-500 font-bold text-xs bg-red-100 px-2 py-1 rounded">Mati</span>
                        @endif
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2 items-center">
                            <form action="{{ route('admin.product.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button class="text-xs font-bold underline {{ $product->pivot->is_active ? 'text-red-500' : 'text-green-600' }}">
                                    {{ $product->pivot->is_active ? 'Matikan' : 'Hidupkan' }}
                                </button>
                            </form>

                            <span class="text-gray-300">|</span>

                            <button type="button" 
                                onclick="openEditModal(
                                    '{{ $product->id }}', 
                                    '{{ addslashes($product->name) }}', 
                                    '{{ $product->pivot->price }}', 
                                    '{{ $product->pivot->stock }}', 
                                    '{{ addslashes($product->description) }}'
                                )"
                                class="text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 px-3 py-1 rounded border border-blue-100 transition">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400">Belum ada menu. Silakan tambah di atas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 text-right">
        <a href="{{ route('admin.dashboard') }}" class="inline-block bg-slate-700 hover:bg-slate-800 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
            <i class="fas fa-check-circle mr-2"></i> Selesai & Kembali ke Dashboard
        </a>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0" id="editModalContent">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-lg text-slate-800">Edit Produk</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <form action="{{ route('admin.product.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="batch_id" value="{{ $batch->id }}">
            <input type="hidden" name="product_id" id="edit_product_id">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Menu</label>
                <input type="text" name="name" id="edit_name" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga (Rp)</label>
                    <input type="number" name="price" id="edit_price" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stok Total (Kapasitas)</label>
                    <input type="number" name="stock" id="edit_stock" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                    <p class="text-[10px] text-gray-400 mt-1">*Masukkan total stok, bukan sisa.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi</label>
                <textarea name="description" id="edit_description" rows="3" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ganti Foto (Opsional)</label>
                <input type="file" name="image" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, price, stock, desc) {
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_stock').value = stock; // Ini mengisi Stok Total di Form
        document.getElementById('edit_description').value = desc;

        const modal = document.getElementById('editModal');
        const content = document.getElementById('editModalContent');
        modal.classList.remove('hidden');
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