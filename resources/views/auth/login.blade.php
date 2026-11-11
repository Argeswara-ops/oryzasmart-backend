<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Oryza IoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 bg-slate-50 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white">

<div class="w-full max-w-sm sm:max-w-md mx-auto">
    <!-- Login Card: Light Mode Bersih dengan Header di Dalam Card -->
    <div class="bg-white border border-slate-200/90 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/60">
        
        <!-- Header di Dalam Card: Icon di Sebelah Judul & Deskripsi Ringkas -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-sm">
                    <i data-lucide="sprout" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Masuk Panel</h1>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 ml-13">Monitoring pembibitan padi cerdas.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl bg-slate-50/80 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                           placeholder="nama@email.com">
                </div>
            </div>

            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                           class="w-full pl-10 pr-10 py-2.5 sm:py-3 rounded-xl bg-slate-50/80 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                           placeholder="••••••••">
                    
                    <!-- Toggle Show/Hide Password Icon -->
                    <button type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors"
                            title="Tampilkan / Sembunyikan Kata Sandi">
                        <span x-show="!showPassword"><i data-lucide="eye" class="w-4 h-4"></i></span>
                        <span x-show="showPassword" x-cloak><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500 pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-50 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-3 sm:py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs sm:text-sm shadow-md shadow-emerald-900/20 hover:shadow-emerald-900/30 transition-all active:scale-[0.99] flex items-center justify-center gap-2 mt-2">
                <span>Masuk Panel</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
</body>
</html>
