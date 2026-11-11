@extends('layouts.admin')

@section('title', 'Monitoring Data Sensor')
@section('page_title', 'Monitoring & Riwayat Telemetri Sensor')

@section('content')
<div class="space-y-6">

    <!-- Top Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pembacaan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_records']) }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Data log tersimpan di database</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-Rata Suhu</span>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['avg_temp'] }} °C</div>
            <p class="text-[11px] text-slate-400 mt-1">Keseluruhan siklus tanam</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-Rata Kelembaban</span>
            <div class="text-2xl font-bold text-cyan-600 mt-1">{{ $stats['avg_humidity'] }} %</div>
            <p class="text-[11px] text-slate-400 mt-1">Kelembaban udara sekitar</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-Rata Air Sawah</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['avg_water'] }} %</div>
            <p class="text-[11px] text-slate-400 mt-1">Estimasi kedalaman air sawah</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.sensors.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label for="device_id" class="text-xs font-semibold text-slate-600">Perangkat:</label>
                <select name="device_id" id="device_id" onchange="this.form.submit()"
                        class="py-2 px-3 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Perangkat</option>
                    @foreach($devices as $dev)
                        <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                            {{ $dev->device_code }} - {{ $dev->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label for="range" class="text-xs font-semibold text-slate-600">Rentang Waktu:</label>
                <select name="range" id="range" onchange="this.form.submit()"
                        class="py-2 px-3 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:border-emerald-500">
                    <option value="all" {{ request('range') === 'all' ? 'selected' : '' }}>Semua Data</option>
                    <option value="today" {{ request('range') === 'today' ? 'selected' : '' }}>Hari Ini (Today)</option>
                    <option value="7days" {{ request('range') === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30days" {{ request('range') === '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                </select>
            </div>

            @if(request()->hasAny(['device_id', 'range']))
                <a href="{{ route('admin.sensors.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline ml-auto">
                    Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Multi-Series Chart Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
            <i data-lucide="line-chart" class="w-5 h-5 text-emerald-600"></i>
            Visualisasi Tren Sensor (50 Sampel Terakhir)
        </h3>

        <div class="h-80 w-full">
            <canvas id="sensorDetailedChart"></canvas>
        </div>
    </div>

    <!-- Telemetry Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Tabel Log Pembacaan Sensor</h3>
            <span class="text-xs text-slate-500">Urut dari waktu terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 uppercase tracking-wider text-[10px] text-slate-500 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Waktu Record</th>
                        <th class="py-3 px-4">Device</th>
                        <th class="py-3 px-4">Suhu (°C)</th>
                        <th class="py-3 px-4">Kelembaban (%)</th>
                        <th class="py-3 px-4">Level Air (%)</th>
                        <th class="py-3 px-4">Jarak (cm)</th>
                        <th class="py-3 px-4">NPK (mg/kg)</th>
                        <th class="py-3 px-4">Cahaya (%)</th>
                        <th class="py-3 px-4">UV Index</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($readings as $row)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                {{ $row->recorded_at ? $row->recorded_at->translatedFormat('d M, H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $row->device ? $row->device->device_code : 'ESP32' }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-amber-600">
                                {{ number_format($row->temperature, 1) }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-cyan-600">
                                {{ $row->humidity }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-600">
                                {{ $row->water_level_percent ?? $row->level_percent ?? '-' }}%
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $row->distance_cm ?? $row->ultrasonic ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <span class="font-bold text-slate-800">{{ $row->nitrogen ?? '-' }}</span> /
                                <span class="font-bold text-slate-800">{{ $row->phosphorus ?? '-' }}</span> /
                                <span class="font-bold text-slate-800">{{ $row->potassium ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $row->light_percent ?? $row->light ?? '-' }}%
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ number_format($row->uv_index, 1) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 px-4 text-center text-slate-400 text-xs">
                                Belum ada data pembacaan sensor pada rentang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($readings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $readings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('sensorDetailedChart').getContext('2d');
        const labels = @json($chartLabels);
        const dataTemp = @json($chartTemp);
        const dataHumidity = @json($chartHumidity);
        const dataWater = @json($chartWater);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Suhu (°C)',
                        data: dataTemp,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.3,
                        borderWidth: 2
                    },
                    {
                        label: 'Kelembaban (%)',
                        data: dataHumidity,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        tension: 0.3,
                        borderWidth: 2
                    },
                    {
                        label: 'Level Air (%)',
                        data: dataWater,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: false, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    });
</script>
@endpush

