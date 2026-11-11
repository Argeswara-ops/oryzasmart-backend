# Oryza Backend & Web Admin Panel

Oryza Backend adalah platform backend berbasis **Laravel 11** dan **PHP 8.4** yang terintegrasi dengan **MQTT (HiveMQ)** untuk sistem pemantauan dan otomasi pembibitan padi pintar berbasis IoT (**ESP32**) serta sinkronisasi dengan aplikasi mobile **Flutter** dan **Web Admin Panel**.

Sistem ini memproses data telemetri multi-sensor lingkungan (suhu, kelembaban, level air sawah, unsur hara NPK, intensitas cahaya, dan indeks UV), status konektivitas perangkat (*heartbeat*), otomasi kontrol aktuator kipas ventilasi berbasis ambang batas (*threshold*), sistem peringatan (*alert*), serta arsip klasifikasi penyakit tanaman padi (*Oryza Vision*).

---

## Fitur Utama

- **Web Admin Panel Modern & Responsif**: Antarmuka kontrol terpadu yang dirancang menggunakan tipografi **Plus Jakarta Sans**, tata letak responsif (*all device: mobile, tablet, desktop*), *copywriting* ringkas, dan performa sinkron dengan database & MQTT.
- **Login Card Bersih (Light Mode)**: Desain kartu autentikasi putih bersih (*light mode*) dengan icon branding berdampingan dengan judul di dalam kartu, serta fitur interaktif *Show/Hide Password*.
- **Sidebar Terfiksasi (Fixed Profile Card)**: Navigasi sidebar dengan kartu profil pengguna yang terfiksasi di bagian bawah (*fixed/pinned*), tidak tergulung saat daftar menu discroll.
- **Popup Notifikasi Interaktif**: Dropdown popup alert pada lonceng navbar dan toast alert dinamis untuk notifikasi kondisi kritis secara *real-time*.
- **Role-Based Access Control (RBAC)**: Pembagian hak akses antara `admin` (akses penuh sistem & manajemen akun) dan `operator` (akses operasional monitoring & aktuator).
- **Telemetri & Visualisasi Data Real-Time**: Grafik multi-series dinamis menggunakan Chart.js untuk memantau tren suhu, kelembaban, dan ketinggian air.
- **Kontrol Aktuator & Audit Log**: Kendali manual (`ON`/`OFF`) dan mode otomatis (`AUTO`) kipas pendingin disertai pencatatan audit log eksekusi sistem vs operator.
- **Ambang Batas Dinamis (Sensor Thresholds)**: Pengaturan batas toleransi minimum dan maksimum untuk 8 parameter sensor dengan fitur *Reset Default*.
- **Peringatan & Rekomendasi Lapangan**: Log anomali otomatis saat kondisi pembibitan melewati batas aman beserta rekomendasi tindakan.
- **Arsip Oryza Vision**: Penyimpanan riwayat diagnosis daun padi (*disease* & *pest*) lengkap dengan skor keyakinan (*confidence*).
- **RESTful API Terlindungi Sanctum**: Endpoint performa tinggi untuk aplikasi Flutter dan sinkronisasi token notifikasi Firebase FCM.
- **Integrasi Protokol MQTT**: Komunikasi dua arah dengan node ESP32 (Wokwi & hardware fisik) melalui broker MQTT HiveMQ.

---

## Web Admin Panel

Panel admin dapat diakses langsung melalui browser setelah server lokal atau production aktif:

- **URL Login**: `http://127.0.0.1:8000/login` atau `http://127.0.0.1:8000/admin`
- **Tipografi**: Plus Jakarta Sans (Google Fonts)
- **Desain UI**: Bersih, light mode modern, responsive drawer sidebar pada perangkat mobile, fixed bottom profile widget.

### Kredensial Akun Dummy Default (Database Seeder)

Database seeder menyediakan akun bawaan siap pakai untuk keperluan *development* dan demonstrasi:

| Role | Alamat Email | Kata Sandi | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@padibackend.test` | `password` | Akses penuh: Manajemen Pengguna, Konfigurasi Perangkat, Ambang Batas, Aktuator, Monitoring Sensor, Log Alert, Arsip Vision |
| **Operator** | `operator@padibackend.test` | `password` | Akses operasional: Dashboard, Monitoring Sensor, Kontrol Kipas, Ambang Batas, Log Alert, Arsip Vision |
| **Teknisi** | `teknisi@padibackend.test` | `password` | Akses operasional pemeliharaan lapangan dan monitoring telemetri |

---

## Modul Panel Admin

1. **Dashboard**: Ringkasan KPI sensor lingkungan (suhu, kelembaban, ketinggian air sawah, NPK, cahaya & UV), status node ESP32 (*online/offline*), tombol kendali cepat kipas, indikator peringatan aktif, toast notification alert, serta grafik tren 30 titik data terakhir.
2. **Monitoring Sensor**: Agregasi statistik rata-rata pembacaan, visualisasi 50 data terakhir, filter fleksibel (hari ini, 7 hari, 30 hari), dan tabel log telemetri dengan pagination.
3. **Kontrol Aktuator**: Kartu kendali status kipas (`Hidupkan`, `Matikan`, `Mode Otomatis`) dan tabel riwayat audit perintah (perintah, sumber pemicu, operator pengeksekusi, dan catatan).
4. **Ambang Batas (Thresholds)**: Konfigurasi batas toleransi aman minimum & maksimum untuk 8 sensor (suhu, kelembaban, ketinggian air, N, P, K, intensitas cahaya, dan UV) beserta tombol reset ke nilai default.
5. **Perangkat IoT (CRUD)**: Manajemen node mikrokontroler (ESP32, kamera, gateway), monitoring status koneksi (*heartbeat* terakhir), dan lokasi penempatan.
6. **Log Peringatan**: Manajemen notifikasi anomali sensor, filter tingkat keparahan (`INFO`, `WARNING`, `CRITICAL`), status baca/belum dibaca, serta tombol *Tandai Semua Dibaca*.
7. **Deteksi Penyakit (Oryza Vision)**: Galeri visual arsip scan daun padi dari aplikasi mobile, filter tipe diagnosis (`disease` / `pest`), label kelas penyakit, dan skor akurasi deteksi.
8. **Manajemen User (CRUD)**: Kelola akun pengguna sistem, tambah operator/admin baru, ubah peran dan kata sandi, dengan proteksi penghapusan akun sendiri (*self-deletion protection*).

---

## Tech Stack

- **Backend Framework**: Laravel 11 / Laravel 13 (PHP 8.4)
- **Database**: MySQL 8.0+ / MariaDB
- **Autentikasi**: Laravel Session Auth (Web Admin) & Laravel Sanctum (Mobile API)
- **IoT & Telemetri**: `php-mqtt/client` (MQTT v3.1.1)
- **Frontend Panel**: Blade Templates, Tailwind CSS, Alpine.js, Lucide Icons, Chart.js
- **Tipografi**: Plus Jakarta Sans
- **Unit & Feature Testing**: PHPUnit 12

---

## Panduan Instalasi & Menjalankan Sistem

### 1. Kloning & Dependensi

```bash
git clone https://github.com/Argeswara-ops/padibackend.git
cd padibackend
composer install
```

### 2. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database dan MQTT pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padibackend
DB_USERNAME=root
DB_PASSWORD=

MQTT_HOST=broker.hivemq.com
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_oryza_smart
MQTT_DEFAULT_DEVICE_ID=ESP32-01
MQTT_SENSOR_TOPIC=oryza-smart/ESP32-01/sensors
MQTT_STATUS_TOPIC=oryza-smart/ESP32-01/status
MQTT_FAN_STATUS_TOPIC=oryza-smart/ESP32-01/actuator/fan/status
MQTT_FAN_COMMAND_TOPIC=oryza-smart/ESP32-01/actuator/fan/command
MQTT_FAN_COMMAND_PAYLOAD=wokwi_json
```

### 3. Migrasi Database & Seeding Akun

Jalankan migrasi tabel beserta *seeder* data dummy lengkap (pengguna, node ESP32 aktif, telemetri sensor, log aktuator, alert, dan hasil vision):

```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Menjalankan Server Web & Listener MQTT

Jalankan web server Laravel:

```bash
php artisan serve
# Atau agar dapat diakses oleh emulator/device eksternal:
php artisan serve --host=0.0.0.0 --port=8000
```

Buka terminal kedua dan jalankan background listener MQTT:

```bash
php artisan mqtt:subscribe
# atau alias:
php artisan mqtt:listen
```

> **Catatan**: Listener MQTT harus tetap berjalan agar data sensor dan *heartbeat* dari ESP32 dapat diproses dan disimpan ke database secara real-time.

---

## Simulator Data IoT (Development)

Untuk menguji fitur tanpa perangkat fisik ESP32, gunakan simulator bawaan:

```bash
# Simulasi injeksi 10 data sensor langsung ke database
php artisan nursery:simulate --count=10 --scenario=mixed

# Simulasi pengiriman data ESP32 melalui broker MQTT HiveMQ
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=mixed --count=10 --interval=1

# Simulasi kondisi kritis dengan feedback kipas ON
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=critical --count=5 --fan-status=ON
```

Pilihan skenario: `normal`, `warning`, `critical`, `mixed`.

---

## Endpoint REST API (Mobile Flutter)

### Publik
- `POST /api/login` : Login mobile dan perolehan Sanctum Bearer Token

### Terproteksi Sanctum (Bearer Token)
- `POST /api/logout` : Revoke token dan logout
- `GET  /api/user` : Profil pengguna yang sedang login
- `GET  /api/dashboard` : Ringkasan data dasbor pembibitan
- `GET  /api/devices` : Daftar status perangkat IoT
- `GET  /api/sensors/latest` : Pembacaan sensor terkini
- `GET  /api/sensors/history` : Riwayat pembacaan sensor
- `GET  /api/thresholds` : Daftar ambang batas sensor
- `PUT  /api/thresholds/{sensorType}` : Pembaruan batas toleransi sensor
- `GET  /api/actuators/fan/status` : Status kipas terkini
- `POST /api/actuators/fan/control` : Perintah kontrol kipas (`ON`/`OFF`/`AUTO`)
- `GET  /api/actuators/logs` : Log audit perintah aktuator
- `GET  /api/alerts` : Daftar notifikasi peringatan anomali
- `POST /api/alerts/{id}/read` : Tandai peringatan telah dibaca
- `POST /api/user/fcm-token` : Pendaftaran Firebase Cloud Messaging token perangkat
- `DELETE /api/user/fcm-token` : Hapus FCM token saat logout
- `POST /api/vision/scans` : Unggah foto daun padi untuk prediksi & sinkronisasi

Dokumentasi spesifikasi API lengkap tersedia di [`docs/API_ENDPOINTS_FLUTTER.md`](docs/API_ENDPOINTS_FLUTTER.md).

---

## Pengujian Otomatis (Testing)

Seluruh fitur backend, autentikasi web, API Sanctum, dan kontrol panel admin telah diuji secara komprehensif:

```bash
php artisan test
```

Hasil pengujian otomatis saat ini: **44/44 tests passed (100% green)**.

---

## Lisensi & Hak Cipta

Dikembangkan oleh **Tim Argeswara / Oryza Smart Paddy Team**. Hak Cipta dilindungi undang-undang.
