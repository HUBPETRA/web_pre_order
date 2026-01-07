@extends('admin.layout')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                {{ $activeBatch->name }}
            </h1>
            <span class="text-sm text-green-600 font-bold bg-green-100 px-2 py-0.5 rounded-full border border-green-200">
                ● Sedang Aktif
            </span>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.batch.menu', $activeBatch->id) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-50 hover:text-blue-600 transition shadow-sm flex items-center gap-2">
                <i class="fas fa-utensils"></i> Menu & Stok
            </a>

            <a href="{{ route('admin.batch.quotas', $activeBatch->id) }}" class="bg-purple-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-purple-700 transition shadow flex items-center gap-2">
                <i class="fas fa-chart-pie"></i> Target Kuota
            </a>

            <a href="{{ route('admin.batch.mail', $activeBatch->id) }}" class="bg-orange-500 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-orange-600 transition shadow flex items-center gap-2">
                <i class="fas fa-envelope"></i> Template Email
            </a>

            <form action="{{ route('admin.batch.close', $activeBatch->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menutup PO ini? User tidak akan bisa pesan lagi.')">
                @csrf
                <button class="bg-red-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-red-700 transition shadow flex items-center gap-2">
                    <i class="fas fa-stop-circle"></i> Tutup PO
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-100 relative group">
            <button onclick="openEditBatchModal()" class="absolute top-3 right-3 text-gray-300 hover:text-blue-600 transition">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Rekening Penerima</p>
            <p class="font-bold text-lg text-blue-900">{{ $activeBatch->bank_name }}</p>
            <p class="text-md text-gray-700 font-mono">{{ $activeBatch->bank_account_number }}</p>
            <p class="text-xs text-gray-500 mt-1">a.n {{ $activeBatch->bank_account_name }}</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-green-100 flex flex-col justify-center relative group">
            <button onclick="openEditBatchModal()" class="absolute top-3 right-3 text-gray-300 hover:text-green-600 transition">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Grup WhatsApp</p>
                    <a href="{{ $activeBatch->whatsapp_link }}" target="_blank" class="text-green-600 font-bold hover:underline text-sm break-all">
                        Link Grup <i class="fas fa-external-link-alt text-xs ml-1"></i>
                    </a>
                </div>
                <i class="fab fa-whatsapp text-4xl text-green-100 text-green-500"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-5 rounded-xl shadow-lg flex flex-col justify-center text-center">
            <p class="text-sm opacity-80 uppercase tracking-wider font-bold">Pesanan Masuk</p>
            <h2 class="text-4xl font-extrabold mt-1">{{ count($orders) }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Validasi Pesanan Baru</h3>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">{{ count($pendingOrders) }} Pending</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="p-4">Pemesan</th>
                                <th class="p-4">Detail Menu</th>
                                <th class="p-4 text-center">Bukti</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pendingOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                                    <div class="text-[10px] text-blue-500 mt-1">Ref: {{ $order->fungsio->name ?? '-' }}</div>
                                </td>
                                <td class="p-4">
                                    <ul class="list-disc pl-4 text-xs text-gray-600 space-y-1">
                                        @foreach($order->orderItems as $item)
                                            <li><span class="font-bold">{{ $item->quantity }}x</span> {{ $item->product_name_snapshot }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ asset('uploads/bukti/' . $order->payment_proof) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                                        <i class="fas fa-image"></i>
                                    </a>
                                </td>
                                <td class="p-4 text-right space-x-1">
                                    <form action="{{ route('admin.order.update', $order->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button name="status" value="Lunas" class="bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-600 hover:text-white transition">Terima</button>
                                        <button name="status" value="Ditolak" class="bg-red-100 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-400 italic bg-gray-50/50">
                                    <i class="fas fa-check-circle mb-2 block text-2xl text-gray-300"></i>
                                    Semua pesanan aman terkendali.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-bold text-gray-700">Stok Menu (Batch Ini)</h3>
                </div>
                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
                    @foreach($activeBatch->products as $product)
                    <div class="border rounded-lg p-3 relative {{ $product->pivot->is_active ? 'bg-white' : 'bg-gray-100 opacity-75' }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800">{{ $product->name }}</h4>
                                <p class="text-xs text-gray-500">Rp {{ number_format($product->pivot->price) }}</p>
                            </div>
                            
                            <form action="{{ route('admin.product.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button class="text-[10px] font-bold px-2 py-1 rounded border transition
                                    {{ $product->pivot->is_active ? 'bg-green-50 text-green-600 border-green-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200' : 'bg-red-50 text-red-600 border-red-200 hover:bg-green-50 hover:text-green-600 hover:border-green-200' }}">
                                    {{ $product->pivot->is_active ? 'Aktif' : 'Habis' }}
                                </button>
                            </form>
                        </div>
                        
                        @php
                            $percent = $product->pivot->stock > 0 ? ($product->pivot->sold / $product->pivot->stock) * 100 : 0;
                            $sisa = $product->pivot->stock - $product->pivot->sold;
                        @endphp
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-1 overflow-hidden">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-wide">
                            <span class="text-blue-600">{{ $product->pivot->sold }} Terjual</span>
                            <span class="{{ $sisa < 5 ? 'text-red-500' : 'text-gray-400' }}">Sisa: {{ $sisa }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="editBatchModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0" id="editBatchContent">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-slate-800">Edit Info PO</h3>
                <button onclick="closeEditBatchModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form action="{{ route('admin.batch.update_info') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $activeBatch->id }}">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Link Grup WhatsApp</label>
                    <input type="url" name="whatsapp_link" value="{{ $activeBatch->whatsapp_link }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" value="{{ $activeBatch->bank_name }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Rekening</label>
                        <input type="number" name="bank_account_number" value="{{ $activeBatch->bank_account_number }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Atas Nama (Rekening)</label>
                    <input type="text" name="bank_account_name" value="{{ $activeBatch->bank_account_name }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>

                <div class="pt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeEditBatchModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditBatchModal() {
            const modal = document.getElementById('editBatchModal');
            const content = document.getElementById('editBatchContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditBatchModal() {
            const modal = document.getElementById('editBatchModal');
            const content = document.getElementById('editBatchContent');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
@endsection