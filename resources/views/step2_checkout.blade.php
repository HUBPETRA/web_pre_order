<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lengkapi Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Scrollbar untuk list item agar rapi */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">

    <nav class="bg-white border-b border-gray-200 py-4 mb-8 sticky top-0 z-30">
        <div class="container mx-auto px-4 max-w-4xl">
            <a href="{{ route('step1') }}" class="text-slate-500 hover:text-blue-600 flex items-center gap-2 transition font-medium group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition"></i> Kembali ke Menu
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 max-w-4xl pb-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="md:col-span-2">
                <form action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                    
                    @csrf
                    <input type="hidden" name="order_details" value="{{ json_encode($selectedItems) }}">

                    <h2 class="font-bold text-xl mb-6 text-slate-800 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span> 
                        Data Pemesan
                    </h2>

                    <div class="mb-5 group">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Nama Lengkap</label>
                        <input type="text" name="customer_name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div class="mb-8 group">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Nomor WhatsApp</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400"><i class="fab fa-whatsapp text-lg"></i></span>
                            <input type="number" name="customer_phone" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" placeholder="Contoh: 0812xxxx" required>
                        </div>
                    </div>

                    <h2 class="font-bold text-xl mb-6 text-slate-800 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span> 
                        Bukti Pembayaran
                    </h2>

                    <div class="mb-6">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Upload Bukti Transfer</label>
                        
                        <div id="drop-zone" class="relative border-2 border-dashed border-blue-300 bg-blue-50 rounded-xl p-8 text-center transition-all duration-300 hover:bg-blue-100 hover:border-blue-500 cursor-pointer group">
                            
                            <input type="file" name="payment_proof" id="file-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/png, image/jpeg, image/jpg" required>
                            
                            <div id="drop-content" class="flex flex-col items-center justify-center space-y-3 pointer-events-none transition-opacity duration-300">
                                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center text-blue-500 group-hover:scale-110 transition">
                                    <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-blue-900">Klik untuk upload <span class="hidden md:inline">atau Drag & Drop</span></p>
                                    <p class="text-xs text-slate-500 mt-1">Format: JPG/PNG (Maks 2MB)</p>
                                </div>
                            </div>

                            <div id="preview-container" class="hidden flex-col items-center justify-center pointer-events-none mt-2">
                                <img id="img-preview" class="h-32 object-contain mb-2 rounded shadow-sm hidden">
                                <p id="file-name" class="text-xs font-bold text-green-600 bg-green-100 px-3 py-1 rounded-full"></p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] active:scale-[0.98]">
                        Konfirmasi Pesanan
                    </button>
                </form>
            </div>

            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-28">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Ringkasan Pesanan</h3>
                    
                    <ul class="space-y-3 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($selectedItems as $item)
                        <li class="flex justify-between text-sm group">
                            <div>
                                <span class="font-medium text-slate-700 block">{{ $item['name'] }}</span>
                                <span class="text-xs text-slate-400">x{{ $item['qty'] }} @ Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </li>
                        @endforeach
                    </ul>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-slate-500 text-sm">Total Bayar</span>
                            <span class="font-bold text-xl text-blue-700">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100 text-center">
                        <p class="text-xs text-slate-400 mb-1">Transfer ke {{ $activeBatch->bank_name }}:</p>
                        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-2 mb-1 inline-block">
                            <p class="font-mono font-bold text-lg text-slate-700 tracking-wider select-all">{{ $activeBatch->bank_account_number }}</p>
                        </div>
                        <p class="text-xs text-slate-500">a.n {{ $activeBatch->bank_account_name }}</p>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fileNameDisplay = document.getElementById('file-name');
        const dropContent = document.getElementById('drop-content');
        const previewContainer = document.getElementById('preview-container');
        const imgPreview = document.getElementById('img-preview');

        // Fungsi Helper: Handle File
        function handleFile(file) {
            if (file) {
                // Tampilkan nama file
                fileNameDisplay.textContent = '✓ ' + file.name;
                
                // Ubah style border jadi hijau
                dropZone.classList.add('border-green-400', 'bg-green-50');
                dropZone.classList.remove('border-blue-300', 'bg-blue-50');

                // Sembunyikan konten default ("Klik untuk upload")
                dropContent.classList.add('hidden');
                
                // Tampilkan container preview
                previewContainer.classList.remove('hidden');
                previewContainer.classList.add('flex');

                // Baca file untuk preview gambar
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        // 1. Saat file dipilih via KLIK
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                handleFile(this.files[0]);
            }
        });

        // 2. Efek Visual Drag Over
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-600', 'bg-blue-100', 'scale-[1.02]');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-600', 'bg-blue-100', 'scale-[1.02]');
        });

        // 3. Saat file di-DROP
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-600', 'bg-blue-100', 'scale-[1.02]');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files; // Assign file ke input
                handleFile(e.dataTransfer.files[0]);    // Jalankan logika preview
            }
        });
    </script>

</body>
</html>