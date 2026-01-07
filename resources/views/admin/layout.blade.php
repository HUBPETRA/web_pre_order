<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dapur Enak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> </head>
<body class="bg-gray-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between h-16">
                
                <div class="flex items-center gap-8">
                    <a href="{{ route('admin.dashboard') }}" class="font-bold text-xl text-blue-900 flex items-center gap-2">
                        <i class="fas fa-utensils"></i> Admin Panel
                    </a>

                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium transition
                           {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.batch.create') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                           Dashboard
                        </a>

                        <a href="{{ route('admin.analytics') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium transition
                           {{ request()->routeIs('admin.analytics') || request()->routeIs('admin.archive') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                           Analitik & Riwayat
                        </a>

                        <a href="{{ route('admin.fungsios.index') }}" 
                            class="px-3 py-2 rounded-md text-sm font-medium transition 
                            {{ request()->routeIs('admin.fungsios.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-blue-600' }}">
                            Manajemen Fungsio
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500 hidden sm:block">Halo, Admin</span>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="bg-red-50 text-red-600 p-2 rounded-full hover:bg-red-600 hover:text-white transition text-xs" title="Logout">
                            <i class="fas fa-power-off"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto p-6 max-w-7xl">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Berhasil!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="container mx-auto px-6 py-4 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Dapur Enak Management System.
        </div>
    </footer>

    @stack('scripts')

</body>
</html>