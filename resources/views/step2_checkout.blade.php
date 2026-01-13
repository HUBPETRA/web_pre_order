<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lengkapi Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">

    <nav class="bg-white border-b border-gray-200 py-4 mb-8 sticky top-0 z-30 shadow-sm">
        <div class="container mx-auto px-4 max-w-4xl">
            <a href="{{ route('step1') }}" class="text-slate-500 hover:text-blue-600 flex items-center gap-2 transition font-medium group text-sm">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition"></i> Kembali ke Menu
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 max-w-4xl pb-12">
        
        {{-- ALERT DRAFT --}}
        <div id="draft-alert" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r shadow-sm animate-pulse">
            <div class="flex items-center">
                <i class="fas fa-history text-yellow-500 mr-3 text-lg"></i>
                <p class="text-sm text-yellow-700">
                    <span class="font-bold">Draft dipulihkan!</span> Kami menemukan data yang belum selesai Anda isi.
                </p>
                <button onclick="clearDraft()" class="ml-auto text-xs text-slate-400 hover:text-red-500 underline font-bold">
                    Hapus Draft
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- KOLOM KANAN (INFO BANK) --}}
            {{-- Di Mobile: Muncul Paling Atas (order-first) --}}
            {{-- Di Desktop: Muncul di Kanan (order-last) --}}
            <div class="md:col-span-1 order-first md:order-last">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                    <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fas fa-receipt text-blue-500"></i> Ringkasan Pesanan
                    </h3>
                    
                    <ul class="space-y-3 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($selectedItems as $item)
                        <li class="flex justify-between text-sm group">
                            <div>
                                <span class="font-medium text-slate-700 block">{{ $item['name'] }}</span>
                                <span class="text-xs text-slate-400">{{ $item['qty'] }}x @ Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </li>
                        @endforeach
                    </ul>

                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 mb-6">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-blue-600 text-sm font-bold">Total Transfer</span>
                            <span class="font-bold text-xl text-blue-800">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100 text-center">
                        <p class="text-xs text-slate-400 mb-2 font-bold uppercase tracking-wider">Silakan Transfer ke:</p>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2 shadow-sm">
                            <p class="text-xs text-gray-500 mb-1 font-bold">{{ $activeBatch->bank_name }}</p>
                            <p class="font-mono font-bold text-lg text-slate-800 tracking-wider select-all cursor-pointer hover:text-blue-600" title="Klik untuk copy" onclick="copyToClipboard('{{ $activeBatch->bank_account_number }}')">
                                {{ $activeBatch->bank_account_number }} <i class="far fa-copy text-xs ml-1 text-gray-400"></i>
                            </p>
                            <p class="text-[10px] text-slate-400 mt-1">a.n {{ $activeBatch->bank_account_name }}</p>
                        </div>
                        <p class="text-[10px] text-gray-400 italic">*Mohon transfer sesuai nominal persis.</p>
                    </div>

                </div>
            </div>

            {{-- KOLOM KIRI (FORM INPUT) --}}
            <div class="md:col-span-2">
                <form id="checkout-form" action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                    
                    @csrf
                    {{-- Hidden input ini opsional jika kamu pakai session cart di controller, tapi bagus untuk debug --}}
                    {{-- <input type="hidden" name="order_details" value="{{ json_encode($selectedItems) }}"> --}}

                    <h2 class="font-bold text-xl mb-6 text-slate-800 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">1</span> 
                        Data Pemesan
                    </h2>

                    <div class="mb-5 group">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Nama Lengkap</label>
                        <input type="text" id="input-name" name="customer_name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div class="mb-5">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Alamat Email</label>
                        <input type="email" id="input-email" name="customer_email" required placeholder="contoh@email.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <p class="text-[10px] text-gray-400 mt-1 ml-1">Bukti pesanan akan dikirim ke email ini.</p>
                    </div>

                    <div class="mb-8 group">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Nomor WhatsApp</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400"><i class="fab fa-whatsapp text-lg"></i></span>
                            {{-- GANTI TYPE NUMBER JADI TEL AGAR TIDAK ADA SPINNER --}}
                            <input type="tel" id="input-phone" name="customer_phone" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition" placeholder="08xxxxxxxx" required pattern="[0-9]*" inputmode="numeric">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Nama Fungsio / Referensi</label>
                        <div class="relative">
                            <select id="input-fungsio" name="fungsio_id" required class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-blue-500 focus:outline-none focus:bg-white transition cursor-pointer">
                                <option value="" disabled selected>-- Pilih Nama Fungsio --</option>
                                @foreach($fungsios as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 ml-1">Pilih nama pengurus yang mereferensikan Anda.</p>
                    </div>

                    <h2 class="font-bold text-xl mb-6 text-slate-800 flex items-center gap-2 pt-4 border-t border-dashed border-gray-100">
                        <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">2</span> 
                        Upload Bukti Pembayaran
                    </h2>

                    <div class="mb-6">
                        <div id="drop-zone" class="relative border-2 border-dashed border-blue-300 bg-blue-50/50 rounded-xl p-8 text-center transition-all duration-300 hover:bg-blue-50 hover:border-blue-500 cursor-pointer group">
                            <input type="file" name="payment_proof" id="file-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/png, image/jpeg, image/jpg" required>
                            
                            <div id="drop-content" class="flex flex-col items-center justify-center space-y-3 pointer-events-none transition-opacity duration-300">
                                <div class="w-14 h-14 bg-white rounded-full shadow-sm flex items-center justify-center text-blue-500 group-hover:scale-110 transition border border-blue-100">
                                    <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Klik untuk upload bukti transfer</p>
                                    <p class="text-xs text-slate-400 mt-1">Format: JPG/PNG (Maks 2MB)</p>
                                </div>
                            </div>

                            <div id="preview-container" class="hidden flex-col items-center justify-center pointer-events-none mt-2">
                                <img id="img-preview" class="h-40 object-contain mb-3 rounded-lg shadow-md border border-gray-200">
                                <p id="file-name" class="text-xs font-bold text-green-700 bg-green-100 px-3 py-1 rounded-full flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> File Terpilih
                                </p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Pesanan
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        // --- LOGIKA COPY TO CLIPBOARD ---
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Nomor rekening berhasil disalin!');
            });
        }

        // --- LOGIKA DRAG & DROP FILE (Existing) ---
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fileNameDisplay = document.getElementById('file-name');
        const dropContent = document.getElementById('drop-content');
        const previewContainer = document.getElementById('preview-container');
        const imgPreview = document.getElementById('img-preview');

        function handleFile(file) {
            if (file) {
                // fileNameDisplay.innerHTML = '<i class="fas fa-check-circle"></i> ' + file.name; // Opsional tampilkan nama
                dropZone.classList.add('border-green-400', 'bg-green-50');
                dropZone.classList.remove('border-blue-300', 'bg-blue-50/50');
                dropContent.classList.add('hidden');
                previewContainer.classList.remove('hidden');
                previewContainer.classList.add('flex');

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) handleFile(this.files[0]);
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-600', 'bg-blue-100');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-600', 'bg-blue-100');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-600', 'bg-blue-100');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files; 
                handleFile(e.dataTransfer.files[0]);    
            }
        });

        // --- LOGIKA AUTO-SAVE DRAFT ---
        
        const formInputs = {
            name: document.getElementById('input-name'),
            email: document.getElementById('input-email'),
            phone: document.getElementById('input-phone'),
            fungsio: document.getElementById('input-fungsio')
        };

        // 1. Simpan ke LocalStorage setiap user mengetik
        Object.keys(formInputs).forEach(key => {
            if(formInputs[key]) { // Cek existensi elemen agar tidak error
                formInputs[key].addEventListener('input', function() {
                    localStorage.setItem('checkout_' + key, this.value);
                });
            }
        });

        // 2. Load data dari LocalStorage saat halaman dibuka
        window.addEventListener('DOMContentLoaded', () => {
            let hasDraft = false;
            Object.keys(formInputs).forEach(key => {
                if(formInputs[key]) {
                    const savedValue = localStorage.getItem('checkout_' + key);
                    if (savedValue) {
                        formInputs[key].value = savedValue;
                        hasDraft = true;
                    }
                }
            });

            // Tampilkan notifikasi jika ada draft ditemukan
            if (hasDraft) {
                document.getElementById('draft-alert').classList.remove('hidden');
            }
        });

        // 3. Bersihkan draft setelah form berhasil disubmit
        document.getElementById('checkout-form').addEventListener('submit', function() {
            Object.keys(formInputs).forEach(key => {
                localStorage.removeItem('checkout_' + key);
            });
        });

        // 4. Fungsi tombol "Hapus Draft" (Manual)
        function clearDraft() {
            Object.keys(formInputs).forEach(key => {
                localStorage.removeItem('checkout_' + key);
                if(formInputs[key]) formInputs[key].value = ''; // Kosongkan input
            });
            document.getElementById('draft-alert').classList.add('hidden');
        }
    </script>

</body>
</html>