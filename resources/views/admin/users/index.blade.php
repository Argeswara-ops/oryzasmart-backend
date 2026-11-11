@extends('layouts.admin')

@section('title', 'Manajemen User')
@section('page_title', 'Manajemen User')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[180px] max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500">
            </div>

            <select name="role" onchange="this.form.submit()" 
                    class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="operator" {{ request('role') === 'operator' ? 'selected' : '' }}>Operator</option>
            </select>

            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.users.create') }}" 
           class="inline-flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-md shadow-emerald-900/20 transition-all">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Tambah User
        </a>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 uppercase tracking-wider text-[10px] text-slate-500 font-semibold">
                    <tr>
                        <th class="py-3.5 px-5">Pengguna</th>
                        <th class="py-3.5 px-5">Role</th>
                        <th class="py-3.5 px-5">Terdaftar</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-slate-500">
                                {{ $user->created_at ? $user->created_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       title="Edit"
                                       class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    @if($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" 
                                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 px-5 text-center text-slate-400 text-xs">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
