<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
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
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 bg-slate-950 text-slate-100 antialiased selection:bg-emerald-500 selection:text-white">

<div class="w-full max-w-sm sm:max-w-md">
    <!-- Header: Ringkas & Bersih Tanpa Icon Branding -->
    <div class="text-center mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Masuk Panel</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1.5">Monitoring pembibitan padi cerdas.</p>
    </div>

    <!-- Login Card: Clean Modern Responsive -->
    <div class="bg-slate-900/90 border border-slate-800/80 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
        @if($errors->any())
            <div class="mb-5 p-3.5 bg-red-950/60 border border-red-800/80 rounded-xl text-red-300 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                           placeholder="nama@email.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400 pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-emerald-500 focus:ring-0">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-3 sm:py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs sm:text-sm shadow-lg shadow-emerald-950/50 hover:shadow-emerald-900/60 transition-all active:scale-[0.99] flex items-center justify-center gap-2 mt-2">
                <span>Masuk Panel</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
