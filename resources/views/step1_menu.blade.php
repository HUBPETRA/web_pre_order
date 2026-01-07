<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Pre-Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-card {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }
        .animate-card:nth-child(1) { animation-delay: 0.1s; }
        .animate-card:nth-child(2) { animation-delay: 0.2s; }
        .animate-card:nth-child(3) { animation-delay: 0.3s; }

        /* Efek Shake saat stok mentok */
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
        .shake { animation: shake 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-white text-slate-800 font-sans pb-32 min-h-screen">

    <nav class="bg-blue-600/90 backdrop-blur-sm shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-white font-bold text-xl flex items-center gap-2">
                <i class="fas fa-utensils bg-white text-blue-600 p-2 rounded-full text-sm"></i> Dapur Enak PO
            </div>
            @if(isset($activeBatch))
                <div class="bg-blue-500 text-white text-xs px-3 py-1 rounded-full border border-blue-400 shadow-sm flex items-center gap-1">
                    <i class="fas fa-tag text-[10px] opacity-80"></i>
                    {{ $activeBatch->name }}
                </div>
            @else
                <div class="bg-gray-500 text-white text-xs px-3 py-1 rounded-full border border-gray-400 shadow-sm">
                    PO Tutup
                </div>
            @endif
        </div>
    </nav>

    @if(session('error'))
    <div class="container mx-auto px-4 mt-6">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm relative" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="text-center py-10 px-4">
        <h1 class="text-3xl md:text-4xl font-bold text-blue-900 mb-2">Mau Makan Apa Hari Ini?</h1>
        <p class="text-slate-500">Pilih menu favoritmu di bawah ini.</p>
    </div>

    <div class="container mx-auto px-4 max-w-5xl">
        <form action="{{ route('step2') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeBatch->products as $product)
                
                @php 
                    // Hitung Sisa Stok
                    $sisaStok = $product->pivot->stock - $product->pivot->sold; 
                    
                    // Jika sisa < 0 (kesalahan DB), anggap 0
                    if($sisaStok < 0) $sisaStok = 0;
                @endphp

                <div class="animate-card bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100 flex flex-col h-full hover:shadow-xl transition-all duration-300 relative">
                    
                    <div class="h-48 bg-slate-200 flex items-center justify-center overflow-hidden relative group">
                        @if($product->image)
                            <img src="{{ asset('uploads/products/' . $product->image) }}" 
                                alt="{{ $product->name }}" 
                                class="w-full h-full object-cover transition duration-300 group-hover:scale-110 {{ $sisaStok == 0 ? 'grayscale' : '' }}">
                        @else
                            <i class="fas fa-image text-5xl opacity-50 text-slate-400"></i>
                        @endif
                        
                        <span class="absolute top-2 right-2 {{ $sisaStok == 0 ? 'bg-red-600' : 'bg-black bg-opacity-60' }} text-white text-xs px-2 py-1 rounded font-bold shadow-sm">
                            {{ $sisaStok == 0 ? 'HABIS' : 'Sisa: ' . $sisaStok }}
                        </span>
                    </div>
                    
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="font-bold text-xl text-slate-800 mb-1 {{ $sisaStok == 0 ? 'text-gray-400 line-through' : '' }}">{{ $product->name }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ $product->desc }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-blue-700 font-extrabold text-lg {{ $sisaStok == 0 ? 'text-gray-400' : '' }}">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            
                            <div class="flex items-center bg-slate-50 rounded-full border border-slate-200 p-1" id="control-{{ $product->id }}">
                                
                                <button type="button" 
                                        onclick="updateQty({{ $product->id }}, -1, {{ $sisaStok }})" 
                                        class="w-8 h-8 flex items-center justify-center bg-white text-slate-600 rounded-full shadow-sm hover:text-red-500 disabled:opacity-50"
                                        {{ $sisaStok == 0 ? 'disabled' : '' }}>
                                    -
                                </button>
                                
                                <input type="number" 
                                       id="input-{{ $product->id }}" 
                                       name="quantities[{{ $product->id }}]" 
                                       value="{{ $cart[$product->id] ?? 0 }}" 
                                       data-price="{{ $product->price }}"
                                       class="qty-input w-10 text-center bg-transparent border-none focus:ring-0 p-0 text-sm font-bold text-slate-700" 
                                       readonly>
                                
                                <button type="button" 
                                        id="btn-plus-{{ $product->id }}"
                                        onclick="updateQty({{ $product->id }}, 1, {{ $sisaStok }})" 
                                        class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                                        {{ $sisaStok == 0 ? 'disabled' : '' }}>
                                    +
                                </button>
                            </div>
                        </div>

                        <div id="msg-{{ $product->id }}" class="hidden text-[10px] text-red-500 font-bold text-center mt-2 animate-pulse">
                            Maksimal stok tercapai!
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

            <div id="sticky-footer" class="fixed bottom-6 left-0 right-0 px-4 z-40 transform translate-y-[150%] transition-transform duration-500">
                <div class="container mx-auto max-w-4xl bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-100 p-4 flex items-center justify-between ring-1 ring-black/5">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Total Estimasi</p>
                        <p class="text-2xl font-bold text-blue-700 leading-none" id="grand-total">Rp 0</p>
                    </div>
                    <button id="btn-next" type="submit" disabled class="bg-gradient-to-r from-blue-600 to-blue-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg cursor-not-allowed grayscale transition-all duration-300">
                        Lanjut Isi Data <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function updateQty(id, change, maxStock) {
            let input = document.getElementById('input-' + id);
            let btnPlus = document.getElementById('btn-plus-' + id);
            let msgBox = document.getElementById('msg-' + id);
            let controlBox = document.getElementById('control-' + id);

            let currentVal = parseInt(input.value);
            let newVal = currentVal + change;

            // Validasi Batas Bawah (0)
            if (newVal < 0) return;

            // Validasi Batas Atas (Stok)
            if (newVal > maxStock) {
                // Efek visual jika stok mentok
                controlBox.classList.add('shake');
                msgBox.classList.remove('hidden');
                setTimeout(() => {
                    controlBox.classList.remove('shake');
                    msgBox.classList.add('hidden');
                }, 500);
                return; // Batalkan penambahan
            }

            // Update Nilai
            input.value = newVal;
            
            // Matikan tombol plus jika sudah mentok
            if (newVal >= maxStock) {
                btnPlus.disabled = true;
                btnPlus.classList.add('bg-gray-400', 'cursor-not-allowed');
                btnPlus.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            } else {
                btnPlus.disabled = false;
                btnPlus.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btnPlus.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }

            calculateTotal();
        }

        function calculateTotal() {
            let inputs = document.querySelectorAll('.qty-input');
            let grandTotal = 0;
            let totalItems = 0;

            inputs.forEach(input => {
                let qty = parseInt(input.value);
                let price = parseInt(input.getAttribute('data-price'));
                if (qty > 0) {
                    grandTotal += (qty * price);
                    totalItems += qty;
                }
            });

            let footer = document.getElementById('sticky-footer');
            let btn = document.getElementById('btn-next');
            document.getElementById('grand-total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');

            if(totalItems > 0) {
                footer.classList.remove('translate-y-[150%]');
                btn.disabled = false;
                btn.classList.remove('grayscale', 'cursor-not-allowed');
            } else {
                footer.classList.add('translate-y-[150%]');
                btn.disabled = true;
                btn.classList.add('grayscale', 'cursor-not-allowed');
            }
        }

        // Panggil saat load untuk cek cart session lama
        document.addEventListener('DOMContentLoaded', () => {
            // Re-check semua tombol plus saat reload (untuk cart lama)
            let inputs = document.querySelectorAll('.qty-input');
            inputs.forEach(input => {
                // Ambil ID dari id="input-123"
                let id = input.id.split('-')[1];
                // Cari tombol plus pasangannya
                let btnPlus = document.getElementById('btn-plus-' + id);
                // Cari max stok dari onclick attribute (sedikit hacky tapi efisien tanpa ubah banyak HTML)
                let onclickText = btnPlus.getAttribute('onclick'); 
                // Regex ambil angka ketiga di updateQty(id, change, maxStock)
                let maxStock = parseInt(onclickText.split(',')[2]);

                if (parseInt(input.value) >= maxStock) {
                    btnPlus.disabled = true;
                    btnPlus.classList.add('bg-gray-400', 'cursor-not-allowed');
                    btnPlus.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                }
            });

            calculateTotal();
        });
    </script>
</body>
</html>