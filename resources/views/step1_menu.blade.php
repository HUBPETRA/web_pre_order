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
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-white text-slate-800 font-sans pb-32 min-h-screen">

    <nav class="bg-blue-600/90 backdrop-blur-sm shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-white font-bold text-xl flex items-center gap-2">
                <i class="fas fa-utensils bg-white text-blue-600 p-2 rounded-full text-sm"></i> Dapur Enak PO
            </div>
            <div class="bg-blue-500 text-white text-xs px-3 py-1 rounded-full border border-blue-400">Batch #1</div>
        </div>
    </nav>

    <div class="text-center py-10 px-4">
        <h1 class="text-3xl md:text-4xl font-bold text-blue-900 mb-2">Mau Makan Apa Hari Ini?</h1>
        <p class="text-slate-500">Pilih menu favoritmu di bawah ini.</p>
    </div>

    <div class="container mx-auto px-4 max-w-5xl">
        <form action="{{ route('step2') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="animate-card bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100 flex flex-col h-full hover:shadow-xl transition-all duration-300">
                    <div class="h-48 bg-slate-200 flex items-center justify-center text-slate-400">
                        <i class="fas fa-image text-5xl opacity-50"></i>
                    </div>
                    
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="font-bold text-xl text-slate-800 mb-1">{{ $product->name }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ $product->desc }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-blue-700 font-extrabold text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            
                            <div class="flex items-center bg-slate-50 rounded-full border border-slate-200 p-1">
                                <button type="button" onclick="updateQty({{ $product->id }}, -1)" class="w-8 h-8 flex items-center justify-center bg-white text-slate-600 rounded-full shadow-sm hover:text-red-500">-</button>
                                
                                <input type="number" 
                                       id="input-{{ $product->id }}" 
                                       name="quantities[{{ $product->id }}]" 
                                       value="{{ $cart[$product->id] ?? 0 }}" 
                                       data-price="{{ $product->price }}"
                                       class="qty-input w-10 text-center bg-transparent border-none focus:ring-0 p-0 text-sm font-bold text-slate-700" 
                                       readonly>
                                
                                <button type="button" onclick="updateQty({{ $product->id }}, 1)" class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700">+</button>
                            </div>
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
        function updateQty(id, change) {
            let input = document.getElementById('input-' + id);
            let newVal = parseInt(input.value) + change;
            if(newVal >= 0) {
                input.value = newVal;
                calculateTotal();
            }
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

        document.addEventListener('DOMContentLoaded', calculateTotal);
    </script>
</body>
</html>