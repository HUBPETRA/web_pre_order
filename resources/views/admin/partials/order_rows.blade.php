@forelse($orders as $order)
<tr class="hover:bg-blue-50 transition border-b border-gray-100 last:border-none">
    
    <td class="p-4 text-gray-500 text-xs align-top whitespace-nowrap">
        {{ $order->created_at->format('d/m/Y') }} <br>
        <span class="text-[10px]">{{ $order->created_at->format('H:i') }}</span>
    </td>

    <td class="p-4 align-top">
        <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
        
        <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
            <i class="fab fa-whatsapp text-green-500"></i> 
            <a href="https://wa.me/{{ $order->customer_phone }}" target="_blank" class="hover:underline hover:text-green-600">
                {{ $order->customer_phone }}
            </a>
        </div>
        
        <div class="mt-1">
            <span class="text-[10px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                Ref: {{ $order->fungsio->name ?? '-' }}
            </span>
        </div>
        <div class="text-[10px] text-gray-400 mt-1">{{ $order->customer_email }}</div>
    </td>

    <td class="p-4 align-top">
        <ul class="list-none space-y-1 text-xs text-gray-600">
            @foreach($order->orderItems as $item)
                <li class="flex items-start gap-2">
                    <span class="font-bold text-slate-800">{{ $item->quantity }}x</span> 
                    <span>{{ $item->product_name_snapshot }}</span>
                </li>
            @endforeach
        </ul>
    </td>

    <td class="p-4 text-center align-top">
        @if($order->status == 'Lunas')
            <span class="bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded text-[10px] font-bold uppercase">Lunas</span>
        @elseif($order->status == 'Ditolak')
            <span class="bg-red-100 text-red-700 border border-red-200 px-2 py-1 rounded text-[10px] font-bold uppercase">Ditolak</span>
        @else
            <span class="bg-yellow-100 text-yellow-700 border border-yellow-200 px-2 py-1 rounded text-[10px] font-bold uppercase">Pending</span>
        @endif
    </td>

    <td class="p-4 text-center align-top">
        @if($order->status == 'Lunas')
            <form action="{{ route('admin.order.toggle_received', $order->id) }}" method="POST">
                @csrf
                @if($order->is_received)
                    {{-- TOMBOL SUDAH DIAMBIL (Hijau) --}}
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded shadow text-xs font-bold w-full transition" title="Klik untuk membatalkan">
                        <i class="fas fa-check mr-1"></i> Sudah
                    </button>
                @else
                    {{-- TOMBOL BELUM DIAMBIL (Abu-abu) --}}
                    <button type="submit" class="bg-gray-100 hover:bg-blue-500 hover:text-white text-gray-500 border border-gray-300 px-3 py-1.5 rounded shadow-sm text-xs font-bold w-full transition" title="Klik jika barang sudah diambil">
                        <i class="fas fa-box mr-1"></i> Belum
                    </button>
                @endif
            </form>
        @else
            <span class="text-gray-300 text-[10px] italic">-</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="p-8 text-center text-gray-400 italic bg-gray-50">
        Tidak ditemukan data pesanan.
    </td>
</tr>
@endforelse