@extends('admin.layout')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Buka Pre-Order Baru</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-red-600 text-sm font-bold flex items-center transition-colors duration-200">
                <i class="fas fa-times mr-1"></i> Batal
            </a>
        </div>

        <form action="{{ route('admin.batch.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @csrf
            
            <div class="p-6 md:p-8 space-y-8">
                <div>
                    <h3 class="font-bold text-lg text-blue-900 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Informasi Kegiatan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kegiatan PO <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('name') border-red-500 @enderror" placeholder="Contoh: PO Januari 2025" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Banner / Gambar Promo (Opsional)</label>
                            <div class="relative">
                                <input type="file" name="banner_image" class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2.5 file:px-4
                                    file:rounded-lg file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    cursor-pointer border border-gray-300 rounded-lg
                                " accept="image/*">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG. Maks: 2MB. Akan ditampilkan di halaman menu user.</p>
                            @error('banner_image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Tutup PO <span class="text-red-500">*</span></label>
                            <input type="date" name="close_date" value="{{ old('close_date') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('close_date') border-red-500 @enderror" required>
                             @error('close_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Pengambilan (Opsional)</label>
                            <input type="date" name="pickup_date" value="{{ old('pickup_date') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('pickup_date') border-red-500 @enderror">
                            <p class="text-xs text-gray-400 mt-1">Boleh dikosongkan jika belum pasti.</p>
                             @error('pickup_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Link Grup WhatsApp <span class="text-red-500">*</span></label>
                            <div class="flex rounded-lg shadow-sm">
                                <span class="bg-green-100 text-green-700 border border-r-0 border-green-200 rounded-l-lg px-3 flex items-center justify-center"><i class="fab fa-whatsapp text-lg"></i></span>
                                <input type="url" name="whatsapp_link" value="{{ old('whatsapp_link') }}" class="w-full border border-gray-300 rounded-r-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('whatsapp_link') border-red-500 @enderror" placeholder="https://chat.whatsapp.com/..." required>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Pastikan link diawali dengan <b>https://</b></p>
                             @error('whatsapp_link')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-lg text-blue-900 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-university"></i> Rekening Pembayaran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Bank <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('bank_name') border-red-500 @enderror" placeholder="BCA / Mandiri" required>
                             @error('bank_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">No. Rekening <span class="text-red-500">*</span></label>
                            <input type="number" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('bank_account_number') border-red-500 @enderror" placeholder="123xxxxx" required>
                             @error('bank_account_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Atas Nama <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('bank_account_name') border-red-500 @enderror" placeholder="Bendahara" required>
                             @error('bank_account_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                    <i class="fas fa-utensils text-blue-600 mt-1 text-lg"></i>
                    <div>
                        <p class="text-sm font-bold text-blue-800">Pengaturan Menu & Kuota</p>
                        <p class="text-xs text-blue-600 mt-1">Anda dapat menambahkan menu makanan dan mengatur kuota fungsio setelah menyimpan data dasar ini.</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200 flex items-center gap-2 transform hover:-translate-y-0.5">
                    Lanjut: Kelola Menu <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </form>
    </div>
@endsection