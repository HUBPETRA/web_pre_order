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

    <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl max-w-md w-full text-center border-t-8 border-blue-600 relative overflow-hidden">
        
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
            <i class="fas fa-check text-4xl text-green-500"></i>
        </div>

        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Pesanan Diterima!</h1>
        <p class="text-slate-500 mb-8 leading-relaxed">
            Terima kasih, data Anda dan bukti transfer telah kami terima. Kami akan segera memverifikasi pesanan Anda.
        </p>
        
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 mb-8 relative z-10">
            <p class="text-sm text-slate-600 mb-3 font-medium">Langkah Terakhir (Wajib):</p>
            <p class="text-xs text-slate-500 mb-4">Silakan bergabung ke grup WhatsApp untuk informasi pengambilan makanan.</p>
            
            @php
                // --- LOGIKA PERBAIKAN LINK YANG LEBIH KUAT ---
                
                // 1. Ambil data, jika null ganti jadi string kosong
                $rawLink = $activeBatch->whatsapp_link ?? ''; 

                // 2. Hapus spasi di depan/belakang (PENTING: User sering tidak sengaja copas spasi)
                $cleanLink = trim($rawLink);

                $finalLink = '#';
                $isLinkValid = false;

                // 3. Cek apakah link ada isinya (lebih dari 3 karakter)
                if (strlen($cleanLink) > 3) {
                    $isLinkValid = true;

                    // 4. Cek awalan http/https. Jika tidak ada, tambahkan.
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
                    <i class="fas fa-link-slash mr-2"></i> Link WA Belum Tersedia
                </button>
            @endif

            {{-- <div class="mt-4 p-2 bg-gray-100 rounded border border-gray-300 text-[10px] text-gray-500 text-left overflow-hidden">
                <strong>DEBUG INFO:</strong><br>
                Raw dari DB: "{{ $rawLink }}" <br>
                Setelah Trim: "{{ $cleanLink }}" <br>
                Link Final: "{{ $finalLink }}" <br>
                Status Valid: {{ $isLinkValid ? 'YA' : 'TIDAK' }}
            </div> --}}
        </div>

        <a href="{{ route('step1') }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm transition relative z-10">
            Buat Pesanan Baru
        </a>
    </div>

</body>
</html>