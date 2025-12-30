<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Order Tutup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="text-center max-w-lg">
        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-400">
            <i class="fas fa-store-slash text-4xl"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-slate-800 mb-3">Pre-Order Sedang Tutup</h1>
        <p class="text-slate-500 mb-8 leading-relaxed">
            Maaf, saat ini tidak ada kegiatan Pre-Order yang sedang berlangsung. Silakan tunggu informasi selanjutnya atau hubungi admin.
        </p>

        <a href="https://wa.me/6281234567890" class="inline-flex items-center gap-2 text-blue-600 font-bold hover:text-blue-800 transition">
            <i class="fab fa-whatsapp"></i> Hubungi Contact Person
        </a>
        
        {{-- <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-xs text-gray-400">Admin? <a href="{{ route('login') }}" class="underline hover:text-gray-600">Login disini</a></p>        </div>
    </div> --}}

</body>
</html>