@extends('admin.layout')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center mb-8">
        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
        <div class="h-1 bg-blue-600 flex-1 mx-2"></div>
        <div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center font-bold">2</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h2 class="text-xl font-bold text-slate-800">Langkah 1: Informasi Kegiatan</h2>
            <p class="text-sm text-gray-500">Isi data dasar Pre-Order sebelum mengatur menu.</p>
        </div>
        
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 border-b border-red-100 text-sm">
                <p class="font-bold flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> Gagal Lanjut:
                </p>
                <ul class="list-disc ml-6 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.batch.storeStep1') }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kegiatan PO</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-lg p-3" placeholder="Contoh: PO Imlek 2025" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Link Grup WhatsApp</label>
                <input type="url" name="whatsapp_link" value="{{ old('whatsapp_link') }}" class="w-full border rounded-lg p-3" placeholder="https://chat.whatsapp.com/..." required>
                <p class="text-xs text-gray-400 mt-1">Wajib diawali dengan <b>https://</b></p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full border rounded-lg p-3" placeholder="BCA" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">No. Rekening</label>
                    <input type="number" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full border rounded-lg p-3" placeholder="123xxxxx" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Atas Nama</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full border rounded-lg p-3" placeholder="Nama" required>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-blue-700">
                    Lanjut ke Menu Produk <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection