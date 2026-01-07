@extends('admin.layout')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Buka Pre-Order Baru</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-red-600 text-sm font-bold">
                <i class="fas fa-times mr-1"></i> Batal
            </a>
        </div>

        <form action="{{ route('admin.batch.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @csrf
            
            <div class="p-8 space-y-8">
                <div>
                    <h3 class="font-bold text-lg text-blue-900 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Informasi Kegiatan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kegiatan PO</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Contoh: PO Januari 2025" required>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Tutup PO</label>
                            <input type="date" name="close_date" value="{{ old('close_date') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Link Grup WhatsApp</label>
                            <div class="flex">
                                <span class="bg-green-100 text-green-700 border border-r-0 border-green-200 rounded-l-lg px-3 flex items-center justify-center"><i class="fab fa-whatsapp"></i></span>
                                <input type="url" name="whatsapp_link" value="{{ old('whatsapp_link') }}" class="w-full border border-gray-300 rounded-r-lg p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="https://chat.whatsapp.com/..." required>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Pastikan link diawali dengan <b>https://</b></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-lg text-blue-900 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-university"></i> Rekening Pembayaran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm" placeholder="BCA / Mandiri" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">No. Rekening</label>
                            <input type="number" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm" placeholder="123xxxxx" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Atas Nama</label>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full border border-gray-300 rounded-lg p-3 text-sm" placeholder="Bendahara" required>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                    <i class="fas fa-utensils text-blue-600 mt-1"></i>
                    <div>
                        <p class="text-sm font-bold text-blue-800">Pengaturan Menu & Kuota</p>
                        <p class="text-xs text-blue-600">Anda dapat menambahkan menu makanan dan mengatur kuota fungsio setelah menyimpan data dasar ini.</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-blue-700 transition flex items-center gap-2">
                    Lanjut: Kelola Menu <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
@endsection