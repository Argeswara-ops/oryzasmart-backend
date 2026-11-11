@extends('layouts.admin')

@section('title', 'Deteksi Penyakit Padi')
@section('page_title', 'Hasil Deteksi Penyakit Tanaman Padi (Oryza Vision)')

@section('content')
<div class="space-y-6">

    <!-- Top Info & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.vision.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama penyakit..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500">
            </div>

            <select name="scan_type" onchange="this.form.submit()" 
                    class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Tipe Scan</option>
                <option value="crop_health" {{ request('scan_type') === 'crop_health' ? 'selected' : '' }}>Crop Health</option>
                <option value="leaf_blast" {{ request('scan_type') === 'leaf_blast' ? 'selected' : '' }}>Leaf Blast</option>
                <option value="brown_spot" {{ request('scan_type') === 'brown_spot' ? 'selected' : '' }}>Brown Spot</option>
            </select>

            @if(request()->hasAny(['search', 'scan_type']))
                <a href="{{ route('admin.vision.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Vision Scans Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($scans as $scan)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between group hover:shadow-md transition-shadow">
                <div>
                    <!-- Image Container -->
                    <div class="h-44 w-full bg-slate-100 relative overflow-hidden flex items-center justify-center">
                        @if($scan->image_path)
                            <img src="{{ asset('storage/' . $scan->image_path) }}" alt="{{ $scan->display_name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-center text-slate-400">
                                <i data-lucide="image-off" class="w-8 h-8 mx-auto mb-1"></i>
                                <span class="text-[10px]">Gambar tidak tersedia</span>
                            </div>
                        @endif

                        <!-- Confidence Badge Overlay -->
                        <div class="absolute top-3 right-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold shadow-md {{ $scan->confidence >= 0.8 ? 'bg-emerald-500 text-white' : ($scan->confidence >= 0.5 ? 'bg-amber-500 text-white' : 'bg-red-500 text-white') }}">
                                {{ round($scan->confidence * 100, 1) }}% Confidence
                            </span>
                        </div>
                    </div>

                    <!-- Scan Details -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
                                {{ $scan->scan_type }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ $scan->scanned_at ? $scan->scanned_at->diffForHumans() : '-' }}
                            </span>
                        </div>

                        <h4 class="font-bold text-slate-900 text-sm mt-2">
                            {{ $scan->display_name ?? $scan->class_name }}
                        </h4>
                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">Label: {{ $scan->class_name }}</p>

                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                            <span>Klien: <strong class="text-slate-700">{{ $scan->user ? $scan->user->name : 'Operator Mobile' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="p-4 pt-0 flex justify-end">
                    <form method="POST" action="{{ route('admin.vision.destroy', $scan) }}" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip foto scan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold p-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200/80 p-6 text-slate-400 text-xs">
                <i data-lucide="scan-line" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                Belum ada arsip riwayat scan penyakit daun yang tersimpan.
            </div>
        @endforelse
    </div>

    @if($scans->hasPages())
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            {{ $scans->links() }}
        </div>
    @endif

</div>
@endsection

