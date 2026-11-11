@extends('layouts.admin')

@section('title', 'Tambah User')
@section('page_title', 'Tambah User')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-base font-bold text-slate-800">Formulir User</h3>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data akun baru.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500"
                       placeholder="Misal: Ahmad Fauzi">
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500"
                       placeholder="nama@domain.com">
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Role Akses</label>
                <select id="role" name="role" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="operator" {{ old('role') === 'operator' ? 'selected' : '' }}>Operator (Akses operasional IoT & Actuator)</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Akses penuh sistem)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required minlength="6"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500"
                           placeholder="Minimal 6 karakter">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500"
                           placeholder="Ulangi password">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-md shadow-emerald-900/20">
                    Simpan User
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
