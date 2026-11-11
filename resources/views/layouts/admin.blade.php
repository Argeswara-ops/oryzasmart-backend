<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Oryza IoT</title>

    <!-- Google Fonts Plus Jakarta Sans -->
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
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
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

    <!-- Sidebar Navigation: Fixed card profil di bawah, tidak ikut scroll -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-200 transition-transform duration-300 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:shrink-0 flex flex-col shadow-xl">
        
        <!-- Brand Header (shrink-0) -->
        <div class="h-20 shrink-0 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950/40">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                    <i data-lucide="sprout" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                        ORYZA <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">IoT</span>
                    </h1>
                    <p class="text-xs text-slate-400">Panel Admin</p>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Navigation Links (flex-1 overflow-y-auto) -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 px-3 mb-2">Menu Utama</div>

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- Sensors Monitoring -->
            <a href="{{ route('admin.sensors.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.sensors.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="activity" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Monitoring Sensor</span>
            </a>

            <!-- Actuators & Fan -->
            <a href="{{ route('admin.actuators.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.actuators.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="fan" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Kontrol Aktuator</span>
            </a>

            <!-- Thresholds -->
            <a href="{{ route('admin.thresholds.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.thresholds.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="sliders" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Ambang Batas</span>
            </a>

            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 px-3 pt-4 mb-2">Analisis Data</div>

            <!-- Vision Scans -->
            <a href="{{ route('admin.vision.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.vision.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="scan" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Deteksi Penyakit</span>
            </a>

            <!-- Devices -->
            <a href="{{ route('admin.devices.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.devices.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="cpu" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Perangkat IoT</span>
            </a>

            <!-- Alerts -->
            <a href="{{ route('admin.alerts.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.alerts.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="bell" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    <span>Log Peringatan</span>
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

            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 px-3 pt-4 mb-2">Pengaturan Sistem</div>

            <!-- Users CRUD -->
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm transition-all {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                <span>Manajemen User</span>
            </a>
        </nav>

        <!-- User Profile Card in Sidebar Bottom: Fixed/Pinned at Bottom -->
        <div class="shrink-0 p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center border border-emerald-500/30 shrink-0">
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
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">@yield('page_title', 'Dashboard')</h2>
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

                <!-- Notification Dropdown Popup -->
                <div class="relative" x-data="{ notifOpen: false }" @click.outside="notifOpen = false">
                    <button @click="notifOpen = !notifOpen" 
                            type="button" 
                            title="Peringatan Sistem"
                            class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors focus:outline-none">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        @if($unreadAlertsBadge > 0)
                            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-amber-500 rounded-full ring-2 ring-white animate-pulse"></span>
                        @endif
                    </button>

                    <!-- Notification Popup Panel -->
                    <div x-show="notifOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-slate-200/90 shadow-2xl z-50 overflow-hidden">
                        
                        <div class="p-3.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="bell-ring" class="w-4 h-4 text-emerald-600"></i>
                                <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Peringatan Sistem</h4>
                            </div>
                            @if($unreadAlertsBadge > 0)
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full">
                                    {{ $unreadAlertsBadge }} Baru
                                </span>
                            @endif
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                            @php
                                $popupAlerts = \App\Models\Alert::with('device')->latest()->take(5)->get();
                            @endphp
                            @forelse($popupAlerts as $pAlert)
                                <div class="p-3.5 hover:bg-slate-50/80 transition-colors {{ $pAlert->status === 'UNREAD' ? 'bg-amber-50/30' : '' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-lg {{ in_array($pAlert->severity, ['CRITICAL', 'DANGER']) ? 'bg-red-100 text-red-600' : ($pAlert->severity === 'WARNING' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-600') }} flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <p class="text-xs font-bold text-slate-900 truncate">{{ $pAlert->title }}</p>
                                                <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                                    {{ $pAlert->created_at ? $pAlert->created_at->diffForHumans(null, true) : '' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $pAlert->message }}</p>
                                            <div class="mt-1 flex items-center justify-between text-[10px]">
                                                <span class="font-mono text-slate-400">{{ $pAlert->device ? $pAlert->device->device_code : 'Sistem' }}</span>
                                                @if($pAlert->status === 'UNREAD')
                                                    <form method="POST" action="{{ route('admin.alerts.read', $pAlert) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-emerald-600 hover:text-emerald-700 font-semibold">Tandai Dibaca</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-slate-400">
                                    <i data-lucide="check-circle" class="w-6 h-6 mx-auto mb-1 text-emerald-500"></i>
                                    Semua sistem berjalan normal
                                </div>
                            @endforelse
                        </div>

                        <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
                            <a href="{{ route('admin.alerts.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                                <span>Lihat Semua Peringatan</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

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
                        <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
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
                        <span class="text-xs sm:text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-800 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-2 font-semibold text-xs sm:text-sm mb-1">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-500"></i>
                        Kesalahan Validasi:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 pl-4 sm:pl-6">
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
            &copy; {{ date('Y') }} Oryza IoT. Monitoring dan Otomasi Pembibitan Padi.
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
