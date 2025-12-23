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

    <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl max-w-md w-full text-center border-t-8 border-blue-600">
        
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-4xl text-green-500"></i>
        </div>

        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Pesanan Diterima!</h1>
        <p class="text-slate-500 mb-8 leading-relaxed">
            Terima kasih, data Anda dan bukti transfer telah kami terima. Admin kami akan segera memverifikasi pesanan Anda.
        </p>
        
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 mb-8">
            <p class="text-sm text-slate-600 mb-3 font-medium">Langkah Terakhir (Wajib):</p>
            <p class="text-xs text-slate-500 mb-4">Silakan bergabung ke grup WhatsApp untuk informasi pengambilan makanan.</p>
            
            <a href="https://chat.whatsapp.com/GantiLinkIni" target="_blank" class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-green-500/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp text-xl"></i> Gabung Grup WhatsApp
            </a>
        </div>

        <a href="{{ route('step1') }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm transition">
            Buat Pesanan Baru
        </a>
    </div>

</body>
</html>