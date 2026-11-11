<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Operator - Oryza IoT Smart Paddy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full flex items-center justify-center p-4 font-sans bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950">

<div class="w-full max-w-md">
    <!-- Brand Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 items-center justify-center text-white shadow-xl shadow-emerald-500/20 mb-4">
            <i data-lucide="sprout" class="w-9 h-9"></i>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">ORYZA SMART PADDY</h1>
        <p class="text-sm text-slate-400 mt-1">Sistem Monitoring & Otomasi IoT Panel Admin</p>
    </div>

    <!-- Login Card -->
    <div class="bg-slate-900/80 backdrop-blur-md border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <h2 class="text-lg font-bold text-white mb-2">Masuk ke Panel Kontrol</h2>
        <p class="text-xs text-slate-400 mb-6">Gunakan akun operator atau administrator Anda untuk melanjutkan.</p>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-red-950/50 border border-red-800/80 rounded-xl text-red-300 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email', 'operator@padibackend.test') }}" required autofocus
                           class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                           placeholder="nama@email.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-0">
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-sm shadow-lg shadow-emerald-900/50 hover:shadow-emerald-900/80 transition-all active:scale-[0.99] flex items-center justify-center gap-2 mt-4">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Development Hint -->
        <div class="mt-6 pt-6 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-400">Akun default seeder:</p>
            <p class="text-xs font-mono text-emerald-400 mt-1">operator@padibackend.test / password</p>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>

