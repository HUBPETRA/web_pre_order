@extends('admin.layout')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
                {{ $activeBatch->name }}
            </h1>
            <span class="text-xs md:text-sm text-green-600 font-bold bg-green-100 px-2 py-0.5 rounded-full border border-green-200 mt-1 inline-block">
                ● Sedang Aktif
            </span>
        </div>
        
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <a href="{{ route('admin.batch.menu', $activeBatch->id) }}" class="flex-1 md:flex-none justify-center bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 hover:text-blue-600 transition shadow-sm flex items-center gap-2">
                <i class="fas fa-utensils"></i> <span class="hidden sm:inline">Menu & Stok</span><span class="sm:hidden">Menu</span>
            </a>

            <a href="{{ route('admin.batch.quotas', $activeBatch->id) }}" class="flex-1 md:flex-none justify-center bg-purple-600 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-purple-700 transition shadow flex items-center gap-2">
                <i class="fas fa-chart-pie"></i> <span class="hidden sm:inline">Target Kuota</span><span class="sm:hidden">Target</span>
            </a>

            <a href="{{ route('admin.batch.mail', $activeBatch->id) }}" class="flex-1 md:flex-none justify-center bg-orange-500 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-orange-600 transition shadow flex items-center gap-2">
                <i class="fas fa-envelope"></i> <span>Email</span>
            </a>

            <form action="{{ route('admin.batch.close', $activeBatch->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menutup PO ini? User tidak akan bisa pesan lagi.')" class="flex-1 md:flex-none">
                @csrf
                <button class="w-full justify-center bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-red-700 transition shadow flex items-center gap-2">
                    <i class="fas fa-stop-circle"></i> <span class="hidden sm:inline">Tutup PO</span><span class="sm:hidden">Tutup</span>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 text-sm font-bold border border-green-200 flex items-center gap-3 shadow-sm">
            <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 text-sm font-bold border border-red-200 flex items-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-xl"></i> Ada kesalahan pada input form Edit Info. Silakan cek kembali.
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 md:gap-6 mb-8">
        
        <div class="bg-white p-4 md:p-5 rounded-xl shadow-sm border border-blue-100 relative group transition hover:shadow-md">
            <button onclick="openEditBatchModal()" class="absolute top-3 right-3 text-gray-300 hover:text-blue-600 transition p-1">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Rekening Penerima</p>
            <p class="font-bold text-lg text-blue-900 truncate" title="{{ $activeBatch->bank_name }}">{{ $activeBatch->bank_name }}</p>
            <p class="text-md text-gray-700 font-mono">{{ $activeBatch->bank_account_number }}</p>
            <p class="text-xs text-gray-500 mt-1 truncate" title="{{ $activeBatch->bank_account_name }}">a.n {{ $activeBatch->bank_account_name }}</p>
        </div>

        <div class="bg-white p-4 md:p-5 rounded-xl shadow-sm border border-green-100 flex flex-col justify-center relative group transition hover:shadow-md">
            <button onclick="openEditBatchModal()" class="absolute top-3 right-3 text-gray-300 hover:text-green-600 transition p-1">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <div class="flex justify-between items-start">
                <div class="overflow-hidden">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Grup WhatsApp</p>
                    <a href="{{ $activeBatch->whatsapp_link }}" target="_blank" class="text-green-600 font-bold hover:underline text-sm block truncate pr-2">
                        Link Grup <i class="fas fa-external-link-alt text-xs ml-1"></i>
                    </a>
                </div>
                <i class="fab fa-whatsapp text-3xl md:text-4xl text-green-100 text-green-500 shrink-0"></i>
            </div>
        </div>

        <div class="bg-white p-4 md:p-5 rounded-xl shadow-sm border border-yellow-100 flex flex-col justify-center relative group transition hover:shadow-md">
             <button onclick="openEditBatchModal()" class="absolute top-3 right-3 text-gray-300 hover:text-yellow-600 transition p-1">
                <i class="fas fa-pen text-xs"></i>
            </button>
            <p class="text-xs text-gray-500 font-bold uppercase mb-1">Jadwal Pengambilan</p>
            @if($activeBatch->pickup_date)
                <p class="font-bold text-lg text-slate-800">{{ $activeBatch->pickup_date->translatedFormat('d M Y') }}</p>
                <p class="text-xs text-gray-400">Pastikan stok siap.</p>
            @else
                <p class="text-sm text-red-500 font-bold italic">Belum ditentukan</p>
                <p class="text-xs text-gray-400">Segera atur.</p>
            @endif
        </div>

        <div class="bg-white p-4 md:p-5 rounded-xl shadow-sm border border-purple-100 flex flex-col justify-center relative group transition hover:shadow-md">
            <button onclick="openEditBatchModal()" class="absolute top-3 right-3 text-gray-300 hover:text-purple-600 transition p-1">
               <i class="fas fa-pen text-xs"></i>
           </button>
           <p class="text-xs text-gray-500 font-bold uppercase mb-2">Banner Promo</p>
           
           @if($activeBatch->banner_image)
               <div class="flex items-center gap-3">
                   <img src="{{ asset('uploads/banners/' . $activeBatch->banner_image) }}" class="w-10 h-10 md:w-12 md:h-12 rounded object-cover border border-gray-200">
                   <div class="overflow-hidden">
                       <p class="text-xs text-green-600 font-bold flex items-center gap-1">
                           <i class="fas fa-check-circle"></i> Terpasang
                       </p>
                       <p class="text-[10px] text-gray-400 truncate">Tampil di menu</p>
                   </div>
               </div>
           @else
               <div class="flex items-center gap-3">
                   <div class="w-10 h-10 md:w-12 md:h-12 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                       <i class="fas fa-image"></i>
                   </div>
                   <div>
                       <p class="text-xs text-red-500 font-bold">Belum Ada</p>
                       <p class="text-[10px] text-gray-400">Upload banner</p>
                   </div>
               </div>
           @endif
       </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-4 md:p-5 rounded-xl shadow-lg flex flex-col justify-center text-center">
            <p class="text-xs md:text-sm opacity-80 uppercase tracking-wider font-bold">Pesanan Masuk</p>
            <h2 class="text-3xl md:text-4xl font-extrabold mt-1">{{ count($orders) }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full max-h-[600px]">
                <div class="px-4 md:px-6 py-4 border-b bg-gray-50 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-gray-700 text-sm md:text-base">Validasi Pesanan Baru</h3>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">{{ count($pendingOrders) }} Pending</span>
                </div>
                
                <div class="overflow-x-auto overflow-y-auto flex-1">
                    <table class="w-full text-sm text-left whitespace-nowrap md:whitespace-normal">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="p-4 bg-gray-50">Pemesan</th>
                                <th class="p-4 bg-gray-50">Detail Menu</th>
                                <th class="p-4 bg-gray-50 text-center">Bukti</th>
                                <th class="p-4 bg-gray-50 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pendingOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 align-top">
                                    <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                                    <div class="text-[10px] text-blue-500 mt-1">Ref: {{ $order->fungsio->name ?? '-' }}</div>
                                </td>
                                <td class="p-4 min-w-[200px] align-top">
                                    <ul class="list-disc pl-4 text-xs text-gray-600 space-y-1">
                                        @foreach($order->orderItems as $item)
                                            <li><span class="font-bold">{{ $item->quantity }}x</span> {{ $item->product_name_snapshot }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="p-4 text-center align-top">
                                    <a href="{{ asset('storage/bukti/' . $order->payment_proof) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                                        <i class="fas fa-image"></i>
                                    </a>
                                </td>
                                <td class="p-4 text-right align-top space-x-1">
                                    <form action="{{ route('admin.order.update', $order->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <div class="flex gap-1 justify-end flex-wrap">
                                            <button name="status" value="Lunas" class="bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-green-600 hover:text-white transition">Terima</button>
                                            <button name="status" value="Ditolak" class="bg-red-100 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">Tolak</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-400 italic bg-gray-50/50">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-check-circle mb-2 text-2xl text-gray-300"></i>
                                        <span>Semua pesanan aman terkendali.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6 h-full max-h-[600px] flex flex-col">
                <div class="px-4 md:px-6 py-4 border-b bg-gray-50 shrink-0">
                    <h3 class="font-bold text-gray-700 text-sm md:text-base">Stok Menu (Batch Ini)</h3>
                </div>
                <div class="p-4 space-y-3 overflow-y-auto flex-1 custom-scrollbar">
                    @foreach($activeBatch->products as $product)
                    <div class="border rounded-lg p-3 relative transition hover:border-blue-300 {{ $product->pivot->is_active ? 'bg-white' : 'bg-gray-100 opacity-75' }}">
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

    <div id="editBatchModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 transition-opacity duration-300 {{ $errors->any() ? '' : 'hidden' }}">
        <div class="bg-white rounded-xl shadow-2xl w-11/12 md:w-full max-w-lg overflow-hidden transform transition-all scale-100 opacity-100 max-h-[90vh] flex flex-col" id="editBatchContent">
            
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50 shrink-0">
                <h3 class="font-bold text-lg text-slate-800">Edit Info PO</h3>
                <button onclick="closeEditBatchModal()" class="text-gray-400 hover:text-red-500 p-2"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <div class="overflow-y-auto p-6">
                <form action="{{ route('admin.batch.update_info') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $activeBatch->id }}">

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Banner Tampilan User</label>
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center {{ $activeBatch->banner_image ? 'bg-white' : 'bg-gray-50' }}">
                            @if($activeBatch->banner_image)
                                <div class="relative inline-block group">
                                    <img src="{{ asset('uploads/banners/' . $activeBatch->banner_image) }}" 
                                         alt="Banner PO" 
                                         class="max-h-32 md:max-h-40 rounded-lg shadow-sm mx-auto object-cover">
                                    <div class="mt-2 text-xs text-green-600 font-bold flex items-center justify-center gap-1">
                                        <i class="fas fa-check-circle"></i> Banner Terpasang
                                    </div>
                                </div>
                            @else
                                <div class="py-4 flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-image text-4xl mb-2 opacity-50"></i>
                                    <span class="text-xs italic">Belum ada banner.</span>
                                </div>
                            @endif

                            <div class="mt-4 text-left">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                                    {{ $activeBatch->banner_image ? 'Ganti Banner' : 'Upload Banner' }}
                                </label>
                                <input type="file" name="banner_image" class="block w-full text-xs text-slate-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-xs file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    border border-gray-300 rounded-lg cursor-pointer
                                " accept="image/*">
                                @error('banner_image')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Pengambilan</label>
                        <input type="date" name="pickup_date" value="{{ $activeBatch->pickup_date ? $activeBatch->pickup_date->format('Y-m-d') : '' }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('pickup_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Link Grup WhatsApp</label>
                        <input type="url" name="whatsapp_link" value="{{ $activeBatch->whatsapp_link }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                        @error('whatsapp_link')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ $activeBatch->bank_name }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                            @error('bank_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Rekening</label>
                            <input type="number" name="bank_account_number" value="{{ $activeBatch->bank_account_number }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                            @error('bank_account_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Atas Nama (Rekening)</label>
                        <input type="text" name="bank_account_name" value="{{ $activeBatch->bank_account_name }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                        @error('bank_account_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t mt-4">
                        <button type="button" onclick="closeEditBatchModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow">Simpan</button>
                    </div>
                </form>
            </div>
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

        // Cek jika ada error dari Laravel (Session flash), otomatis buka modal
        @if($errors->any())
            openEditBatchModal();
        @endif
    </script>
@endsection