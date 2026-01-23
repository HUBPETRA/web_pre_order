<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-blue-50 flex items-center justify-center min-h-screen p-4">

    {{-- Ubah max-w-md menjadi max-w-lg agar muat untuk tabel rincian --}}
    <div class="bg-white p-8 md:p-10 rounded-2xl shadow-xl max-w-lg w-full text-center border-t-8 border-blue-600 relative overflow-hidden my-5">
        
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
            <i class="fas fa-check text-4xl text-green-500"></i>
        </div>

        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Pesanan Diterima!</h1>
        <p class="text-slate-500 mb-6 text-sm leading-relaxed">
            Terima kasih <b>{{ $order->customer_name }}</b>, data pesanan dan bukti pembayaran Anda telah kami terima. Pesanan Anda akan segera diverifikasi.
        </p>

        {{-- [BARU] BAGIAN 1: Rincian Pesanan --}}
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 mb-6 text-left shadow-inner">
            <h3 class="font-bold text-gray-700 text-sm border-b border-gray-300 pb-2 mb-2 flex justify-between items-center">
                <span><i class="fas fa-receipt mr-1 text-gray-400"></i> ID Pesanan</span>
                <span class="font-mono text-xs text-gray-500">#{{ $order->id }}</span>
            </h3>
            <ul class="space-y-2 text-sm text-gray-600 mb-3">
                @foreach($order->orderItems as $item)
                <li class="flex justify-between items-start">
                    <span>{{ $item->quantity }}x {{ $item->product_name_snapshot }}</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </li>
                @endforeach
            </ul>
            <div class="border-t border-gray-300 pt-2 flex justify-between items-center font-bold text-blue-700">
                <span>Total Bayar</span>
                <span class="text-lg">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- [BARU] BAGIAN 2: Info Pengambilan --}}
        <div class="bg-blue-50 rounded-xl border border-blue-100 p-4 mb-8 text-left flex items-start gap-3">
            <div class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center shrink-0 mt-1">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h4 class="font-bold text-blue-900 text-sm">Jadwal Pengambilan</h4>
                
                {{-- LOGIKA TAMPILAN DARI DATABASE --}}
                @if($activeBatch && $activeBatch->pickup_date)
                    <p class="text-blue-800 font-bold text-sm mt-1">
                        {{-- Format: Senin, 20 Januari 2025 --}}
                        {{ \Carbon\Carbon::parse($activeBatch->pickup_date)->translatedFormat('l, d F Y') }}
                    </p>
                    <p class="text-blue-600 text-xs mt-0.5">
                        <i class="fas fa-map-marker-alt mr-1"></i> Lokasi: {{ $activeBatch->pickup_location ?? 'Cek Grup WA' }}
                    </p>
                @else
                    <p class="text-blue-600 text-xs mt-1 italic">
                        Tanggal belum ditentukan. Pantau Grup WhatsApp.
                    </p>
                @endif
            </div>
        </div>
        
        {{-- BAGIAN 3: Link WhatsApp (Logic Lama Anda) --}}
        <div class="bg-white border-2 border-dashed border-green-300 p-5 rounded-xl mb-6 relative z-10">
            <p class="text-sm text-slate-600 mb-3 font-medium">Langkah Terakhir (Wajib):</p>
            <p class="text-xs text-slate-500 mb-4">Silakan bergabung ke grup WhatsApp untuk informasi pengambilan makanan.</p>
            
            @php
                // --- LOGIKA PERBAIKAN LINK YANG LEBIH KUAT ---
                $rawLink = $activeBatch->whatsapp_link ?? ''; 
                $cleanLink = trim($rawLink);
                $finalLink = '#';
                $isLinkValid = false;

                if (strlen($cleanLink) > 3) {
                    $isLinkValid = true;
                    if (str_starts_with($cleanLink, 'http://') || str_starts_with($cleanLink, 'https://')) {
                        $finalLink = $cleanLink;
                    } else {
                        $finalLink = 'https://' . $cleanLink;
                    }
                }
            @endphp

            @if($isLinkValid)
                <a href="{{ $finalLink }}" target="_blank" class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-green-500/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                    <i class="fab fa-whatsapp text-xl"></i> Gabung Grup WhatsApp
                </a>
            @else
                <button disabled class="block w-full bg-gray-300 text-white font-bold py-3 px-6 rounded-lg cursor-not-allowed opacity-70">
                    <i class="fas fa-link-slash mr-2"></i> Link WhatsApp Belum Tersedia
                </button>
            @endif
        </div>

        <a href="{{ route('step1') }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm transition relative z-10 underline decoration-blue-300 underline-offset-4 hover:decoration-blue-600">
            &larr; Buat Pesanan Baru
        </a>
    </div>

</body>
</html>