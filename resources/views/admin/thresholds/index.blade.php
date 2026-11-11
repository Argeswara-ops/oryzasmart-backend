@extends('layouts.admin')

@section('title', 'Ambang Batas')
@section('page_title', 'Ambang Batas')

@section('content')
<div class="space-y-6">

    <!-- Info Banner -->
    <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-4 sm:p-5 text-emerald-900 text-xs flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-sm text-emerald-950 mb-0.5">Panduan Ambang</p>
            <p class="leading-relaxed text-emerald-800">
                Batas toleransi sensor digunakan untuk memicu peringatan otomatis dan kendali kipas.
            </p>
        </div>
    </div>

    <!-- Threshold Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
        @php
            $sensorMeta = [
                'temperature' => ['name' => 'Suhu Udara', 'unit' => '°C', 'icon' => 'thermometer'],
                'humidity' => ['name' => 'Kelembaban Udara', 'unit' => '%', 'icon' => 'droplets'],
                'water_level' => ['name' => 'Ketinggian Air', 'unit' => '%', 'icon' => 'waves'],
                'nitrogen' => ['name' => 'Nitrogen (N)', 'unit' => 'mg/kg', 'icon' => 'sprout'],
                'phosphorus' => ['name' => 'Phosphorus (P)', 'unit' => 'mg/kg', 'icon' => 'leaf'],
                'potassium' => ['name' => 'Potassium (K)', 'unit' => 'mg/kg', 'icon' => 'flower-2'],
                'light_percent' => ['name' => 'Intensitas Cahaya', 'unit' => '%', 'icon' => 'sun'],
                'uv_index' => ['name' => 'Indeks UV', 'unit' => 'UV', 'icon' => 'shield-alert'],
            ];
        @endphp

        @foreach($sensorMeta as $type => $meta)
            @php
                $item = $thresholds->get($type);
                $minVal = $item ? $item->min_value : 0;
                $maxVal = $item ? $item->max_value : 100;
            @endphp
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                                <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4 text-emerald-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs">{{ $meta['name'] }}</h4>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $type }}</span>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                            {{ $meta['unit'] }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.thresholds.update', $type) }}" class="space-y-3 mt-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Batas Min</label>
                                <input type="number" step="0.1" name="min_value" value="{{ old('min_value', $minVal) }}" required
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Batas Max</label>
                                <input type="number" step="0.1" name="max_value" value="{{ old('max_value', $maxVal) }}" required
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="pt-2 flex items-center gap-2">
                            <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold transition-colors">
                                Simpan
                            </button>
                        </div>
                    </form>

                    <div class="pt-1.5">
                        <form method="POST" action="{{ route('admin.thresholds.reset', $type) }}">
                            @csrf
                            <button type="submit" title="Reset Default" class="w-full py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-medium transition-colors flex items-center justify-center gap-1.5">
                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                <span>Reset Default</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Rentang aman:</span>
                    <span class="font-bold text-slate-700">{{ $minVal }} - {{ $maxVal }} {{ $meta['unit'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
