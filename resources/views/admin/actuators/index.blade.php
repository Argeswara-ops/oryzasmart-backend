@extends('layouts.admin')

@section('title', 'Kontrol Aktuator')
@section('page_title', 'Kontrol Aktuator')

@section('content')
<div class="space-y-6">

    <!-- Actuators Control Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        @forelse($actuators as $actuator)
            @php
                $isOn = $actuator->status === 'ON';
                $isAuto = $actuator->mode === 'AUTO';
            @endphp
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $isOn ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                                <i data-lucide="fan" class="w-6 h-6 {{ $isOn ? 'animate-spin' : '' }}"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">{{ $actuator->name }}</h3>
                                <p class="text-[11px] text-slate-500">Device: {{ $actuator->device ? $actuator->device->device_code : 'ESP32' }}</p>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ $isOn ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $actuator->status }}
                        </span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 mb-4 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Mode Sistem</span>
                            <span class="font-bold text-slate-800">{{ $actuator->mode }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tipe Hardware</span>
                            <span class="font-semibold text-slate-700 capitalize">{{ $actuator->type }}</span>
                        </div>
                    </div>

                    <!-- Direct Control Form -->
                    <form method="POST" action="{{ route('admin.actuators.control') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="actuator_id" value="{{ $actuator->id }}">

                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="command" value="ON" 
                                    class="py-2.5 px-3 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5 {{ $isOn ? 'bg-emerald-700 text-white shadow' : 'bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700' }}">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                Hidupkan
                            </button>
                            <button type="submit" name="command" value="OFF"
                                    class="py-2.5 px-3 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5 {{ !$isOn ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-red-600 hover:text-white text-slate-700' }}">
                                <i data-lucide="power-off" class="w-3.5 h-3.5"></i>
                                Matikan
                            </button>
                        </div>

                        <button type="submit" name="command" value="AUTO"
                                class="w-full py-2.5 px-3 rounded-xl border border-slate-200 hover:bg-slate-100 font-semibold text-xs text-slate-700 flex items-center justify-center gap-1.5">
                            <i data-lucide="bot" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Mode Otomatis
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8 bg-white rounded-2xl border border-slate-200 p-6 text-slate-400 text-xs">
                Belum ada data aktuator yang terdaftar di database.
            </div>
        @endforelse
    </div>

    <!-- Actuator Logs Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Log Perintah</h3>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat eksekusi sistem dan operator.</p>
            </div>

            <!-- Filter By Source -->
            <form method="GET" action="{{ route('admin.actuators.index') }}" class="flex items-center gap-2">
                <select name="source" onchange="this.form.submit()"
                        class="py-1.5 px-3 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Sumber</option>
                    <option value="USER" {{ request('source') === 'USER' ? 'selected' : '' }}>USER (Operator)</option>
                    <option value="SYSTEM" {{ request('source') === 'SYSTEM' ? 'selected' : '' }}>SYSTEM (Otomatis)</option>
                </select>
                @if(request('source'))
                    <a href="{{ route('admin.actuators.index') }}" class="text-xs text-slate-500 underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 uppercase tracking-wider text-[10px] text-slate-500 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Aktuator</th>
                        <th class="py-3 px-4">Perintah</th>
                        <th class="py-3 px-4">Sumber</th>
                        <th class="py-3 px-4">Operator</th>
                        <th class="py-3 px-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                {{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $log->actuator ? $log->actuator->name : 'Aktuator' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ ($log->command ?? $log->action) === 'ON' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $log->command ?? $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $log->source === 'USER' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $log->source }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $log->user ? $log->user->name : ($log->source === 'SYSTEM' ? 'Otomasi Sistem' : '-') }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $log->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400 text-xs">
                                Belum ada log eksekusi aktuator yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
