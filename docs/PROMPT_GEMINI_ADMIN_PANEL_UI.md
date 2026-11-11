# Prompt Gemini: UI Panel Admin - Sistem IoT Monitoring & Otomasi Padi (Oryza)

Dokumen ini berisi panduan prompt ringkas dan terstruktur yang siap disalin (*copy-paste*) ke **Google Gemini** untuk mengenerate kode dan tampilan **UI Panel Admin Web** yang terintegrasi dengan REST API backend Laravel (`padibackend`).

---

## 📋 Cara Penggunaan
1. Buka [Google Gemini](https://gemini.google.com/).
2. Salin salah satu prompt di bawah (**Prompt Utama** untuk hasil lengkap & modern, atau **Prompt Ringkas** untuk prototype cepat).
3. Anda dapat menyesuaikan stack frontend yang diinginkan (default: **React / Next.js + Tailwind CSS + Lucide Icons + Recharts**).

---

## 🚀 1. Prompt Utama (Rekomendasi - Komprehensif & Terstruktur)

```markdown
Bertindaklah sebagai Senior Frontend & UI/UX Engineer. Buatkan kode UI Admin Panel Dashboard berbasis web yang modern, clean, dan responsif untuk sistem "IoT Smart Paddy Monitoring & Automation (Oryza)".

### 1. Spesifikasi Teknis:
- Stack: React / Next.js (App Router), Tailwind CSS, Lucide React (icons), dan Recharts (grafik sensor).
- Tema Desain: Modern Clean Dashboard bertema Agricultural Tech (Dominan Emerald / Green, Slate/Gray neutral, aksen Amber/Red untuk alert). Dukung dark mode & light mode.
- Arsitektur Komponen: Modular, reusable component, state management bersih, dan mockup data yang siap dihubungkan ke REST API.

### 2. Struktur Navigasi & Menu (Sidebar):
1. 📊 Dashboard Utama (Ringkasan KPI real-time, status ESP32, kontrol cepat kipas).
2. 📈 Monitoring Sensor & Grafik (Suhu, Kelembaban, NPK, Ketinggian Air %, Intensitas Cahaya, UV Index dengan filter Today / 7 Days / 30 Days).
3. ⚙️ Kontrol Aktuator (Kipas pendingin: Mode AUTO/MANUAL, Switch ON/OFF, tabel log aktivitas aktuator).
4. 🎚️ Manajemen Ambang Batas (Form konfigurasi Min & Max threshold per jenis sensor).
5. 🌾 Oryza Vision & Deteksi Penyakit (Upload foto daun padi, hasil klasifikasi AI penyakit, skor confidence %, dan rekomendasi penanganan).
6. 📟 Manajemen Perangkat (Tabel ESP32: Device Code, Lokasi, Status ONLINE/OFFLINE, Last Seen heartbeat).
7. 🔔 Log Alert & Notifikasi (Daftar peringatan kritis/peringatan sensor, filter belum dibaca, tombol mark-as-read).

### 3. Komponen Utama Halaman Dashboard:
- Header: Status koneksi ESP32 badge (ONLINE hijau / OFFLINE merah pulsing), dropdown profil operator, badge notifikasi alert belum dibaca.
- Quick Metrics Cards:
  * Suhu (°C) & Kelembaban (%)
  * Ketinggian Air Sawah (% & cm)
  * Unsur Tanah N-P-K (Nitrogen, Phosphorus, Potassium dlm mg/kg)
  * Cahaya (%) & UV Index
- Quick Control Card: Status Kipas (ON/OFF), Mode (AUTO/MANUAL switch), tombol manual trigger.
- Grafik Realtime: Tren suhu & kelembaban 24 jam terakhir menggunakan Recharts.
- Recent Alerts Widget: 3-5 notifikasi anomali sensor terkini.

### 4. Format Integrasi API Backend Laravel (Sanctum Auth):
- Base URL: `/api` dengan Bearer Token.
- Endpoint referensi:
  * Auth: POST `/login`, GET `/user`
  * Dashboard: GET `/dashboard`
  * Sensor: GET `/sensors/latest`, GET `/sensors/history?range={today|7days|30days}`
  * Device: GET `/devices`, GET `/devices/{id}/status`
  * Aktuator: POST `/actuators/fan/control` `{ "command": "ON|OFF", "mode": "AUTO|MANUAL" }`, GET `/actuators/logs`
  * Ambang Batas: GET `/thresholds`, PUT `/thresholds/{sensorType}`
  * Vision & Diagnosis: POST `/vision/scans`, POST `/paddy/predict`
  * Alerts: GET `/alerts`, POST `/alerts/{id}/read`

Berikan kode halaman Dashboard utama (`page.tsx`), komponen Sidebar responsif, Card metrik sensor, serta contoh integrasi `fetch`/`axios` yang rapi dan terstruktur.
```

---

## ⚡ 2. Prompt Ringkas (One-Shot / Cepat)

Jika membutuhkan respon cepat dengan fokus prototipe:

```markdown
Buatkan halaman dashboard admin web (React + Tailwind CSS + Lucide Icons) untuk sistem monitoring lahan sawah berbasis IoT (Oryza).

Fitur yang harus ada dalam 1 tampilan dashboard:
1. Sidebar navigasi (Dashboard, Sensor, Kontrol Kipas, Ambang Batas, Deteksi Penyakit Daun, Perangkat).
2. Header dengan indikator status ESP32 (Online/Offline) dan lonceng notifikasi alert.
3. Metric Cards: Suhu (32.4°C), Kelembaban (88%), Air Sawah (72%), NPK (26-25-35), UV Index (3.2).
4. Kontrol Aktuator Kipas: Toggle Switch ON/OFF dan pilihan Mode AUTO / MANUAL.
5. Grafik Recharts interaktif untuk tren suhu & kelembaban air.
6. Tabel riwayat peringatan (Alerts) dengan status normal/warning/danger.

Desain elegan, modern, bertema smart agriculture (warna hijau emerald & slate). Sertakan kode TypeScript/React yang siap dijalankan.
```

---

## 🧩 3. Varian Alternatif: Laravel Filament / Blade (Monolith)

Jika ingin membangun panel admin langsung di dalam folder `padibackend` tanpa framework frontend terpisah:

```markdown
Saya memiliki backend Laravel 11 untuk sistem IoT monitoring sawah ("IOT-PADI").
Buatkan panduan implementasi dan kode panel admin menggunakan Laravel Filament v3 / Blade + Tailwind CSS untuk mengelola:
1. Resource Device (ESP32) dengan indikator status Online/Offline berdasarkan last_seen.
2. Widget Dashboard menampilkan data sensor realtime (Suhu, Kelembaban, Level Air, NPK).
3. Action button untuk kontrol kipas (Kirim perintah ON/OFF/AUTO ke database/MQTT).
4. Resource SensorThreshold untuk setting nilai batas minimum dan maksimum tiap sensor.
5. Resource VisionScan untuk melihat riwayat deteksi foto penyakit padi beserta rekomendasinya.
6. Notifikasi bell untuk unread Alerts.

Sertakan kode Filament Resource / Controller & View Blade yang clean dan siap pakai.
```

---

## 📚 Ringkasan Endpoint Backend (Konteks Cepat)

Bila Gemini memerlukan konteks payload API, lampirkan tabel ringkas ini:

| Modul | Method | Endpoint | Keterangan |
| :--- | :--- | :--- | :--- |
| **Auth** | `POST` | `/api/login` | Login operator/admin |
| **Dashboard** | `GET` | `/api/dashboard` | Rekap sensor, fan status, device status |
| **Sensors** | `GET` | `/api/sensors/latest` | Pembacaan terakhir semua sensor |
| **Sensors** | `GET` | `/api/sensors/history?range=today` | Riwayat grafik (today, 7days, 30days) |
| **Thresholds** | `GET` / `PUT` | `/api/thresholds/{sensorType}` | Ambil & update batas sensor |
| **Actuators** | `POST` | `/api/actuators/fan/control` | Kontrol manual/auto kipas pendingin |
| **Actuators** | `GET` | `/api/actuators/logs` | Riwayat aktivitas kipas |
| **Devices** | `GET` | `/api/devices` | Daftar perangkat ESP32 |
| **Vision** | `POST` | `/api/vision/scans` | Upload foto daun untuk deteksi penyakit |
| **Alerts** | `GET` / `POST`| `/api/alerts`, `/api/alerts/{id}/read`| Daftar & tandai alert terbaca |
| **Recommendations** | `GET` | `/api/recommendations` | Tips & rekomendasi perawatan padi |

