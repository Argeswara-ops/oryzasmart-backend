@extends('layouts.admin')

@section('title', 'Edit Perangkat')
@section('page_title', 'Edit Perangkat: ' . $device->device_code)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.devices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Perangkat
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-base font-bold text-slate-800">Ubah Konfigurasi Perangkat</h3>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi node mikrokontroler.</p>
        </div>

        <form method="POST" action="{{ route('admin.devices.update', $device) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="device_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Kode Perangkat</label>
                    <input type="text" id="device_code" name="device_code" value="{{ old('device_code', $device->device_code) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 font-mono focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Perangkat</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $device->name) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tipe Perangkat</label>
                    <select id="type" name="type" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="esp32" {{ old('type', $device->type) === 'esp32' ? 'selected' : '' }}>ESP32 Node (Sensor & Kipas)</option>
                        <option value="camera" {{ old('type', $device->type) === 'camera' ? 'selected' : '' }}>Kamera / Vision</option>
                        <option value="gateway" {{ old('type', $device->type) === 'gateway' ? 'selected' : '' }}>Gateway</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Status</label>
                    <select id="status" name="status" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="ONLINE" {{ old('status', $device->status) === 'ONLINE' ? 'selected' : '' }}>ONLINE</option>
                        <option value="OFFLINE" {{ old('status', $device->status) === 'OFFLINE' ? 'selected' : '' }}>OFFLINE</option>
                        <option value="ERROR" {{ old('status', $device->status) === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="location" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi Penempatan</label>
                <input type="text" id="location" name="location" value="{{ old('location', $device->location) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.devices.index') }}" class="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-md shadow-emerald-900/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

