@extends('layouts.admin')

@section('title', 'Perangkat Smart')
@section('page_title', 'Perangkat Smart')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs text-slate-500">Daftar node mikrokontroler dan gateway pembibitan.</p>
        <a href="{{ route('admin.devices.create') }}" 
           class="inline-flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-md shadow-emerald-900/20 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Perangkat
        </a>
    </div>

    <!-- Devices Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 uppercase tracking-wider text-[10px] text-slate-500 font-semibold">
                    <tr>
                        <th class="py-3.5 px-5">Perangkat</th>
                        <th class="py-3.5 px-5">Tipe & Lokasi</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5">Koneksi Terakhir</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $device)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center shrink-0">
                                        <i data-lucide="cpu" class="w-5 h-5 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 font-mono">{{ $device->device_code }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $device->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                    {{ $device->type }}
                                </span>
                                <p class="text-[11px] text-slate-600 mt-1">{{ $device->location ?? 'Belum diset' }}</p>
                            </td>
                            <td class="py-4 px-5">
                                @if($device->status === 'ONLINE')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        ONLINE
                                    </span>
                                @elseif($device->status === 'ERROR')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        ERROR
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        OFFLINE
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-slate-500">
                                {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Belum terhubung' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.devices.edit', $device) }}" 
                                       title="Edit"
                                       class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.devices.destroy', $device) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat {{ $device->device_code }}?')">
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
                                Belum ada perangkat terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
