@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- Suhu & Kelembaban -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Suhu Udara</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="thermometer" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">
                    {{ $latestSensor ? number_format($latestSensor->temperature, 1) : '--' }}
                </span>
                <span class="text-sm font-semibold text-slate-500">°C</span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                <i data-lucide="droplets" class="w-3.5 h-3.5 text-blue-500"></i>
                <span>Kelembaban: <strong class="text-slate-800">{{ $latestSensor ? $latestSensor->humidity : '--' }}%</strong></span>
            </div>
        </div>

        <!-- Ketinggian Air Sawah -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ketinggian Air</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="waves" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-blue-600">
                    {{ $latestSensor ? ($latestSensor->water_level_percent ?? $latestSensor->level_percent ?? '--') : '--' }}
                </span>
                <span class="text-sm font-semibold text-slate-500">%</span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                <i data-lucide="ruler" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Jarak Sensor: <strong class="text-slate-800">{{ $latestSensor ? ($latestSensor->distance_cm ?? $latestSensor->ultrasonic ?? '--') : '--' }} cm</strong></span>
            </div>
        </div>

        <!-- Unsur Tanah (NPK) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Tanah</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="leaf" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-1.5 py-1 text-center">
                <div class="bg-slate-50 rounded-lg p-1.5 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block">N</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-800">{{ $latestSensor ? $latestSensor->nitrogen : '--' }}</span>
                </div>
                <div class="bg-slate-50 rounded-lg p-1.5 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block">P</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-800">{{ $latestSensor ? $latestSensor->phosphorus : '--' }}</span>
                </div>
                <div class="bg-slate-50 rounded-lg p-1.5 border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block">K</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-800">{{ $latestSensor ? $latestSensor->potassium : '--' }}</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 text-center">mg/kg tanah sawah</p>
        </div>

        <!-- Cahaya & Indeks UV -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cahaya UV</span>
                <div class="w-9 h-9 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center">
                    <i data-lucide="sun" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">
                    {{ $latestSensor ? ($latestSensor->light_percent ?? $latestSensor->light ?? '--') : '--' }}
                </span>
                <span class="text-sm font-semibold text-slate-500">% Cahaya</span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>Indeks UV: <strong class="text-slate-800">{{ $latestSensor ? number_format($latestSensor->uv_index, 1) : '--' }}</strong></span>
            </div>
        </div>

    </div>

    <!-- Middle Section: ESP32 Status & Quick Actuator Control -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">

        <!-- Device Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i data-lucide="cpu" class="w-5 h-5 text-emerald-600"></i>
                            Perangkat IoT
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Status koneksi node.</p>
                    </div>
                    @if($primaryDevice && $primaryDevice->status === 'ONLINE')
                        <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            ONLINE
                        </span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            OFFLINE
                        </span>
                    @endif
                </div>

                <div class="space-y-3 text-xs sm:text-sm">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Kode Perangkat</span>
                        <span class="font-semibold text-slate-800 font-mono">{{ $primaryDevice ? $primaryDevice->device_code : 'ESP32-01' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Nama Unit</span>
                        <span class="font-semibold text-slate-800">{{ $primaryDevice ? $primaryDevice->name : 'ESP32 Utama' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Lokasi Penempatan</span>
                        <span class="font-semibold text-slate-800">{{ $primaryDevice ? $primaryDevice->location : 'Rumah Kaca' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-500">Heartbeat Terakhir</span>
                        <span class="font-medium text-slate-700">
                            {{ $primaryDevice && $primaryDevice->last_seen ? $primaryDevice->last_seen->diffForHumans() : 'Belum terhubung' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('admin.devices.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                    Semua Perangkat ({{ $totalDevices }})
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Quick Actuator Fan Control Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i data-lucide="fan" class="w-5 h-5 text-teal-600"></i>
                            Kontrol Kipas
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Ventilasi dan aerasi.</p>
                    </div>
                    @php
                        $fanIsOn = $fan && $fan->status === 'ON';
                        $fanMode = $fan ? $fan->mode : 'AUTO';
                    @endphp
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $fanIsOn ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $fan ? $fan->status : 'OFF' }}
                    </span>
                </div>

                <div class="p-4 rounded-xl {{ $fanIsOn ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' }} mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $fanIsOn ? 'bg-emerald-600 text-white animate-spin' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center shrink-0">
                            <i data-lucide="fan" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-500 uppercase font-semibold">Mode Operasi</p>
                            <p class="text-sm sm:text-base font-bold text-slate-900">{{ $fanMode }}</p>
                        </div>
                    </div>
                </div>

                @if($fan)
                    <form method="POST" action="{{ route('admin.actuators.control') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="actuator_id" value="{{ $fan->id }}">

                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="command" value="ON" 
                                    class="py-2.5 px-3 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5 {{ $fanIsOn ? 'bg-emerald-700 text-white ring-2 ring-emerald-500' : 'bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700' }}">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                HIDUPKAN
                            </button>
                            <button type="submit" name="command" value="OFF"
                                    class="py-2.5 px-3 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5 {{ !$fanIsOn ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-red-600 hover:text-white text-slate-700' }}">
                                <i data-lucide="power-off" class="w-3.5 h-3.5"></i>
                                MATIKAN
                            </button>
                        </div>

                        <div>
                            <button type="submit" name="command" value="AUTO"
                                    class="w-full py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 font-semibold text-xs text-slate-700 flex items-center justify-center gap-1.5">
                                <i data-lucide="bot" class="w-3.5 h-3.5 text-emerald-600"></i>
                                Mode Otomatis
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-xs text-slate-500">Aktuator belum terdaftar.</p>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.actuators.index') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-700 flex items-center gap-1">
                    Log Eksekusi
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- System Summary & Quick Actions -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl p-6 text-white shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-white text-base mb-1 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i>
                    Status Sistem
                </h3>
                <p class="text-xs text-slate-400 mb-4">Indikator operasional.</p>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="bell" class="w-4 h-4 text-amber-400"></i>
                            <span class="text-xs text-slate-300">Peringatan Aktif</span>
                        </div>
                        <span class="font-bold text-sm {{ $unreadAlertsCount > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                            {{ $unreadAlertsCount }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="users" class="w-4 h-4 text-teal-400"></i>
                            <span class="text-xs text-slate-300">Total Pengguna</span>
                        </div>
                        <span class="font-bold text-sm text-white">
                            {{ $totalUsersCount }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/60 border border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="scan" class="w-4 h-4 text-emerald-400"></i>
                            <span class="text-xs text-slate-300">Total Scan</span>
                        </div>
                        <span class="font-bold text-sm text-white">
                            {{ $totalScansCount }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800 flex gap-2">
                <a href="{{ route('admin.thresholds.index') }}" class="flex-1 text-center py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 transition-colors">
                    Ambang Batas
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex-1 text-center py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white transition-colors">
                    Kelola User
                </a>
            </div>
        </div>

    </div>

    <!-- Sensor Trends Chart -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i data-lucide="line-chart" class="w-5 h-5 text-emerald-600"></i>
                    Tren Sensor
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat 30 titik data terakhir.</p>
            </div>
            <a href="{{ route('admin.sensors.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                Data Lengkap
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="h-72 w-full">
            <canvas id="dashboardSensorChart"></canvas>
        </div>
    </div>

    <!-- Bottom Split: Recent Alerts & Recent Vision Scans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">

        <!-- Recent Alerts -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i data-lucide="bell-ring" class="w-5 h-5 text-amber-500"></i>
                        Peringatan Terkini
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Notifikasi kritis pembibitan.</p>
                </div>
                <a href="{{ route('admin.alerts.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Semua Alert</a>
            </div>

            <div class="space-y-3">
                @forelse($recentAlerts as $alert)
                    <div class="p-3.5 rounded-xl border {{ $alert->status === 'UNREAD' ? 'border-amber-200 bg-amber-50/40' : 'border-slate-200 bg-slate-50' }} flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg {{ in_array($alert->severity, ['CRITICAL', 'DANGER']) ? 'bg-red-100 text-red-600' : ($alert->severity === 'WARNING' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-600') }} flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">{{ $alert->title }}</h4>
                                <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2">{{ $alert->message }}</p>
                                <span class="text-[10px] text-slate-400 font-mono mt-1 block">
                                    {{ $alert->created_at ? $alert->created_at->diffForHumans() : '' }}
                                </span>
                            </div>
                        </div>

                        @if($alert->status === 'UNREAD')
                            <form method="POST" action="{{ route('admin.alerts.read', $alert) }}">
                                @csrf
                                <button type="submit" title="Tandai dibaca" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-400"></i>
                        Tidak ada peringatan aktif.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Vision Scans -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i data-lucide="scan" class="w-5 h-5 text-teal-600"></i>
                        Deteksi Daun
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Diagnosis penyakit tanaman.</p>
                </div>
                <a href="{{ route('admin.vision.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Semua Scan</a>
            </div>

            <div class="space-y-3">
                @forelse($recentScans as $scan)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 flex items-center justify-center text-slate-500 overflow-hidden shrink-0">
                                @if($scan->image_path)
                                    <img src="{{ asset('storage/' . $scan->image_path) }}" alt="scan" class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">{{ $scan->display_name ?? $scan->class_name }}</h4>
                                <p class="text-[11px] text-slate-500">Tipe: {{ $scan->scan_type }}</p>
                                <span class="text-[10px] text-slate-400">{{ $scan->scanned_at ? $scan->scanned_at->diffForHumans() : '' }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-full {{ $scan->confidence >= 0.8 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ round($scan->confidence * 100, 1) }}%
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">Akurasi</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        <i data-lucide="scan-line" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        Belum ada riwayat scan penyakit.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('dashboardSensorChart').getContext('2d');
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
                        borderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Kelembaban (%)',
                        data: dataHumidity,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        tension: 0.3,
                        borderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Level Air (%)',
                        data: dataWater,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        borderWidth: 2,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 } }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' } }
                    }
                }
            }
        });
    });
</script>
@endpush
