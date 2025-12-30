@if($batches->isEmpty())
    <tr>
        <td colspan="4" class="p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                <i class="fas fa-search text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-600">Tidak Ditemukan</h3>
            <p class="text-gray-400 text-sm mt-1">
                Tidak ada data yang cocok dengan pencarianmu.
            </p>
        </td>
    </tr>
@else
    @foreach($batches as $batch)
    <tr class="hover:bg-blue-50 transition group border-b border-gray-50 last:border-0">
        <td class="p-4">
            <div class="font-bold text-slate-800">{{ $batch->name }}</div>
            <div class="sm:hidden text-xs text-gray-500 mt-1">{{ $batch->created_at->format('d M Y') }}</div>

            @if(request('q'))
                @php
                    $matchedProducts = $batch->products->filter(function($product) {
                        return stripos($product->name, request('q')) !== false;
                    });
                @endphp

                @if($matchedProducts->isNotEmpty())
                    <div class="mt-2 text-xs text-gray-500 bg-yellow-50 border border-yellow-100 p-1.5 rounded-lg inline-block animate-pulse">
                        <span class="font-bold text-yellow-700"><i class="fas fa-search"></i> Ditemukan di menu:</span> 
                        @foreach($matchedProducts as $product)
                            <span class="bg-white px-1 rounded border border-gray-200 text-slate-600 ml-1">
                                {{ $product->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            @endif
        </td>
        <td class="p-4 text-gray-600 hidden sm:table-cell">{{ $batch->created_at->format('d M Y') }}</td>
        <td class="p-4">
            @if($batch->is_active)
                <span class="bg-green-100 text-green-700 border border-green-200 px-2 py-1 rounded text-xs font-bold">● Aktif</span>
            @else
                <span class="bg-gray-100 text-gray-500 border border-gray-200 px-2 py-1 rounded text-xs font-bold">Arsip</span>
            @endif
        </td>
        <td class="p-4 text-right">
            <a href="{{ route('admin.archive.detail', $batch->id) }}" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 hover:border-blue-300 transition">
                <i class="fas fa-eye"></i> Detail
            </a>
        </td>
    </tr>
    @endforeach
    
    @if($batches->hasPages())
    <tr>
        <td colspan="4" class="p-4 bg-gray-50">
            {{ $batches->appends(['q' => request('q')])->links() }}
        </td>
    </tr>
    @endif
@endif