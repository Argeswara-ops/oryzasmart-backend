<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Oryza Smart Paddy</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

<div class="min-h-full flex flex-col lg:flex-row">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity">
    </div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-200 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col shadow-xl">
        
        <!-- Brand Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950/40">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                    <i data-lucide="sprout" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                        ORYZA <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">IoT</span>
                    </h1>
                    <p class="text-xs text-slate-400">Admin Control Panel</p>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 px-3 mb-2">Menu Utama</div>

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- Sensors Monitoring -->
            <a href="{{ route('admin.sensors.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.sensors.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="activity" class="w-5 h-5"></i>
                <span>Monitoring Sensor</span>
            </a>

            <!-- Actuators & Fan -->
            <a href="{{ route('admin.actuators.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.actuators.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="fan" class="w-5 h-5"></i>
                <span>Kontrol Kipas & Log</span>
            </a>

            <!-- Thresholds -->
            <a href="{{ route('admin.thresholds.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.thresholds.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="sliders" class="w-5 h-5"></i>
                <span>Ambang Batas (Threshold)</span>
            </a>

            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 px-3 pt-4 mb-2">Analisis & Device</div>

            <!-- Vision Scans -->
            <a href="{{ route('admin.vision.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.vision.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="scan" class="w-5 h-5"></i>
                <span>Deteksi Penyakit (Vision)</span>
            </a>

            <!-- Devices -->
            <a href="{{ route('admin.devices.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.devices.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="cpu" class="w-5 h-5"></i>
                <span>Perangkat ESP32</span>
            </a>

            <!-- Alerts -->
            <a href="{{ route('admin.alerts.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.alerts.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span>Log Peringatan (Alerts)</span>
                </div>
                @php
                    $unreadAlertsBadge = \App\Models\Alert::where('status', 'UNREAD')->count();
                @endphp
                @if($unreadAlertsBadge > 0)
                    <span class="px-2 py-0.5 text-xs font-bold bg-amber-500 text-slate-950 rounded-full">
                        {{ $unreadAlertsBadge }}
                    </span>
                @endif
            </a>

            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 px-3 pt-4 mb-2">Pengaturan Sistem</div>

            <!-- Users CRUD -->
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span>Manajemen User</span>
            </a>
        </nav>

        <!-- User Profile Card in Sidebar Bottom -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center border border-emerald-500/30">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="truncate max-w-[120px]">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ Auth::user()->role ?? 'operator' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
        <!-- Top Navbar -->
        <header class="h-20 bg-white border-b border-slate-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">@yield('page_title', 'Dashboard')</h2>
            </div>

            <div class="flex items-center gap-3">
                <!-- ESP32 Device Quick Status -->
                @php
                    $espDevice = \App\Models\Device::where('type', 'esp32')->first();
                    $isOnline = $espDevice && $espDevice->status === 'ONLINE';
                @endphp
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full {{ $isOnline ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                    <span class="text-xs font-semibold">{{ $espDevice ? $espDevice->device_code : 'ESP32' }}: {{ $isOnline ? 'ONLINE' : 'OFFLINE' }}</span>
                </div>

                <!-- Alerts Shortcut -->
                <a href="{{ route('admin.alerts.index') }}" class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    @if($unreadAlertsBadge > 0)
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-amber-500 rounded-full ring-2 ring-white"></span>
                    @endif
                </a>

                <!-- Date / Time -->
                <div class="hidden md:block text-xs font-medium text-slate-500 pl-2 border-l border-slate-200">
                    {{ now()->translatedFormat('l, d M Y') }}
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 lg:px-8 pt-6">
            @if(session('success'))
                <div class="mb-4 flex items-center justify-between p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center justify-between p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-800 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-500"></i>
                        Terjadi kesalahan validasi input:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 pl-6">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Body Content -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 pb-12">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="py-4 px-6 border-t border-slate-200/80 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Oryza IoT Monitoring & Otomasi Lahan Padi. Built with Laravel 11.
        </footer>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@stack('scripts')
</body>
</html>

