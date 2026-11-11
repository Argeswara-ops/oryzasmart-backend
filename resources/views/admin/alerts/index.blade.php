@extends('layouts.admin')

@section('title', 'Peringatan & Alert')
@section('page_title', 'Log Peringatan & Notifikasi Anomali')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.alerts.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau pesan..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500">
            </div>

            <select name="status" onchange="this.form.submit()" 
                    class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Status</option>
                <option value="UNREAD" {{ request('status') === 'UNREAD' ? 'selected' : '' }}>Belum Dibaca</option>
                <option value="READ" {{ request('status') === 'READ' ? 'selected' : '' }}>Sudah Dibaca</option>
            </select>

            <select name="severity" onchange="this.form.submit()" 
                    class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Tingkat (Severity)</option>
                <option value="INFO" {{ request('severity') === 'INFO' ? 'selected' : '' }}>INFO</option>
                <option value="WARNING" {{ request('severity') === 'WARNING' ? 'selected' : '' }}>WARNING</option>
                <option value="CRITICAL" {{ request('severity') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
            </select>

            @if(request()->hasAny(['search', 'status', 'severity']))
                <a href="{{ route('admin.alerts.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>

        @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.alerts.read_all') }}">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 py-2 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-sm transition-all">
                    <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                    Tandai Semua Sudah Dibaca ({{ $unreadCount }})
                </button>
            </form>
        @endif
    </div>

    <!-- Alerts Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 uppercase tracking-wider text-[10px] text-slate-500 font-semibold">
                    <tr>
                        <th class="py-3.5 px-5">Tingkat / Waktu</th>
                        <th class="py-3.5 px-5">Peringatan</th>
                        <th class="py-3.5 px-5">Sumber / Device</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $alert->status === 'UNREAD' ? 'bg-amber-50/20' : '' }}">
                            <td class="py-4 px-5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider inline-block {{ in_array($alert->severity, ['CRITICAL', 'DANGER']) ? 'bg-red-100 text-red-700 border border-red-200' : ($alert->severity === 'WARNING' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-700 border border-blue-200') }}">
                                    {{ $alert->severity }}
                                </span>
                                <p class="text-[10px] text-slate-400 mt-1 font-mono">
                                    {{ $alert->created_at ? $alert->created_at->translatedFormat('d M, H:i') : '-' }}
                                </p>
                            </td>

                            <td class="py-4 px-5 max-w-md">
                                <p class="font-bold text-slate-900 text-xs">{{ $alert->title }}</p>
                                <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">{{ $alert->message }}</p>
                                @if($alert->recommendation)
                                    <div class="mt-2 p-2 rounded-lg bg-emerald-50 border border-emerald-100 text-[11px] text-emerald-800">
                                        <strong class="font-semibold">Rekomendasi Tindakan:</strong> {{ $alert->recommendation->action_plan ?? $alert->recommendation->text }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-4 px-5 text-slate-600">
                                <p class="font-semibold text-slate-800">{{ $alert->device ? $alert->device->device_code : 'Sistem' }}</p>
                                <p class="text-[10px] text-slate-400 uppercase">{{ $alert->source ?? 'Sensor Anomaly' }}</p>
                            </td>

                            <td class="py-4 px-5">
                                @if($alert->status === 'UNREAD')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Belum Dibaca
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                        Dibaca
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if($alert->status === 'UNREAD')
                                        <form method="POST" action="{{ route('admin.alerts.read', $alert) }}">
                                            @csrf
                                            <button type="submit" title="Tandai Sudah Dibaca" 
                                                    class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.alerts.destroy', $alert) }}" 
                                          onsubmit="return confirm('Hapus riwayat peringatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" 
                                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-5 text-center text-slate-400 text-xs">
                                Tidak ada log peringatan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alerts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

