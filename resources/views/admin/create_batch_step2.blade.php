@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <div class="flex items-center mb-8">
        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold"><i class="fas fa-check"></i></div>
        <div class="h-1 bg-blue-600 flex-1 mx-2"></div>
        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded flex justify-between items-center">
            <div>
                <strong class="font-bold"><i class="fas fa-check-circle"></i> Berhasil!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 font-bold px-2">x</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded relative">
            <strong class="font-bold"><i class="fas fa-exclamation-triangle"></i> Gagal Menyimpan:</strong>
            <ul class="list-disc ml-5 text-sm mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById('productModal').classList.remove('hidden');
                });
            </script>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kelola Menu: {{ $batch->name }}</h1>
            <p class="text-gray-500 text-sm">Tambahkan menu makanan yang akan dijual pada periode ini.</p>
        </div>
        
        <button onclick="document.getElementById('productModal').classList.remove('hidden')" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        @if($batch->products->isEmpty())
            <div class="text-center p-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-600">Belum ada produk</h3>
                <p class="text-gray-400 text-sm">Klik tombol "Tambah Produk" di atas untuk mulai.</p>
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase">
                    <tr>
                        <th class="p-4">Nama Produk</th>
                        <th class="p-4">Harga</th>
                        <th class="p-4">Stok</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($batch->products as $product)
                    <tr>
                        <td class="p-4">
                            <div class="font-bold">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500">{{ $product->description }}</div>
                        </td>
                        <td class="p-4">Rp {{ number_format($product->pivot->price) }}</td>
                        <td class="p-4 font-bold">{{ $product->pivot->stock }}</td>
                        <td class="p-4 text-right text-green-600">
                            <i class="fas fa-check-circle"></i> Siap
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <form action="{{ route('admin.batch.publish', $batch->id) }}" method="POST" class="flex justify-end">
        @csrf
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg flex items-center gap-2 transform transition hover:-translate-y-1">
            <i class="fas fa-rocket"></i> Selesai & Buka PO Sekarang
        </button>
    </form>
</div>

<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all my-8">
        
        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-bold text-lg text-slate-800">Tambah Menu Baru</h3>
            <button onclick="document.getElementById('productModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.batch.addProduct') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="batch_id" value="{{ $batch->id }}">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Foto Produk <span class="text-red-500">*</span></label>
                <div class="flex items-center justify-center w-full">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 overflow-hidden relative">
                        
                        <div id="upload-text-container" class="flex flex-col items-center justify-center pt-5 pb-6 transition-opacity duration-300">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                            <p class="text-sm text-gray-500 text-center px-2 mb-1"><span class="font-bold">Klik untuk pilih</span> gambar</p>
                            <p class="text-xs text-gray-400">JPG/PNG (MAX. 2MB)</p>
                        </div>

                        <img id="image-preview" class="absolute inset-0 w-full h-full object-cover hidden p-1 rounded-lg">

                        <input id="dropzone-file" type="file" name="image" class="hidden" accept="image/png, image/jpeg, image/jpg" required onchange="previewImage(event)" />
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Makanan</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-lg p-2.5" placeholder="Contoh: Ayam Bakar Madu" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi</label>
                <textarea name="description" class="w-full border rounded-lg p-2.5" rows="2" placeholder="Potongan paha, sambal terpisah..." required>{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga Jual</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs text-gray-500">Rp</span>
                        <input type="number" name="price" value="{{ old('price') }}" class="w-full border rounded-lg pl-8 p-2.5" placeholder="15000" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stok Awal</label>
                    <input type="number" name="stock" value="{{ old('stock') }}" class="w-full border rounded-lg p-2.5" placeholder="50" required>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg">
                    Simpan & Tambahkan
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('image-preview');
        const textContainer = document.getElementById('upload-text-container');

        // Cek apakah ada file yang dipilih
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            // Saat file selesai dibaca, jalankan fungsi ini
            reader.onload = function(e) {
                preview.src = e.target.result;      // Masukkan data gambar ke src
                preview.classList.remove('hidden'); // Munculkan gambar
                textContainer.classList.add('opacity-0'); // Sembunyikan teks instruksi
            }

            // Baca file sebagai URL Data
            reader.readAsDataURL(input.files[0]);
        } else {
            // Jika user batal memilih file
            preview.src = '';
            preview.classList.add('hidden');
            textContainer.classList.remove('opacity-0');
        }
    }
</script>

@endsection