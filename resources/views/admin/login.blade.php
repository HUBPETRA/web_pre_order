<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Genta PO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-tr from-blue-900 to-slate-800 h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl flex overflow-hidden max-w-4xl w-full">
        
        <div class="hidden md:flex w-1/2 bg-blue-600 items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-pattern opacity-10"></div> <div class="text-white text-center z-10">
                <div class="bg-white/20 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 backdrop-blur-sm">
                    <i class="fas fa-utensils text-4xl"></i>
                </div>
                <h2 class="text-3xl font-bold mb-2">Genta PO</h2>
                <p class="text-blue-100">Management System</p>
            </div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-70"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-70"></div>
        </div>

        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <h3 class="text-2xl font-bold text-slate-800 mb-1">Selamat Datang!</h3>
            <p class="text-slate-500 mb-8 text-sm">Silakan login untuk mengelola pesanan.</p>

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded text-sm flex items-start gap-2">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-2">Email Admin</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-slate-400"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="" required>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-slate-400"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-bold py-3 rounded-lg shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
                    Masuk ke Dashboard
                </button>
            </form>
            
            <p class="text-center text-xs text-slate-400 mt-8">&copy; {{ date('Y') }} Genta PO Official</p>
        </div>
    </div>
</body>
</html>