# Simulasi IoT MQTT

Panduan ini dipakai untuk simulasi perangkat ESP32 dengan Mosquitto pada project backend ini.

## Konfigurasi MQTT

Pastikan `.env` backend mengarah ke broker Mosquitto lokal:

```env
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_padibackend
MQTT_USERNAME=
MQTT_PASSWORD=
```

Kalau `.env` berubah, bersihkan cache config:

```powershell
php artisan config:clear
```

## Topic Yang Dipakai Project

Backend mendengar topic berikut:

```text
rice-nursery/+/sensors
rice-nursery/+/status
rice-nursery/+/actuator/+/status
```

Untuk device default `ESP32-01`, topic-nya menjadi:

```text
rice-nursery/ESP32-01/sensors
rice-nursery/ESP32-01/status
rice-nursery/ESP32-01/actuator/fan/status
```

Backend mengirim command kipas ke:

```text
rice-nursery/ESP32-01/actuator/fan/command
rice-nursery/ESP32-01/actuator/fan/mode
```

## 1. Jalankan Mosquitto

Jika Mosquitto sudah terinstall sebagai service Windows, pastikan servicenya aktif.

Untuk cek cepat dengan Docker:

```powershell
docker run -it --rm -p 1883:1883 eclipse-mosquitto:2 mosquitto -c /mosquitto-no-auth.conf
```

Jika memakai Mosquitto lokal Windows, pastikan command ini bisa jalan:

```powershell
mosquitto_sub -h 127.0.0.1 -t "test" -v
```

Kalau `mosquitto_sub` atau `mosquitto_pub` tidak dikenali, jalankan dari folder instalasi Mosquitto atau tambahkan folder Mosquitto ke `PATH`.

## 2. Jalankan Listener Laravel

Buka terminal di root project backend:

```powershell
php artisan mqtt:listen
```

Terminal ini harus tetap hidup selama simulasi. Jika ada payload masuk, listener akan mencetak pesan yang diterima.

## 3. Simulasi ESP32 Dengan Command Project

Buka terminal kedua di root project backend.

Simulasi data campuran normal, warning, dan critical:

```powershell
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=mixed --count=30 --interval=2
```

Simulasi kondisi normal:

```powershell
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=normal --count=10 --interval=2
```

Simulasi kondisi warning:

```powershell
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=warning --count=10 --interval=2
```

Simulasi kondisi critical:

```powershell
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=critical --count=10 --interval=2
```

Simulasi sensor sekaligus feedback status kipas dari ESP32:

```powershell
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=critical --count=10 --interval=2 --fan-status=ON
```

Opsi command:

```powershell
php artisan mqtt:simulate-device --help
```

## 4. Cek Data Di Flutter

Setelah simulator berjalan, buka aplikasi Flutter dan refresh halaman dashboard/sensor.

Data yang dikirim oleh simulator akan diproses oleh listener Laravel, lalu tersimpan sebagai sensor reading untuk endpoint:

```text
GET /api/sensors/latest
GET /api/sensors/history
GET /api/dashboard
```

## 5. Tes Kontrol Kipas Dari Flutter

Buka terminal baru untuk melihat command kipas yang dikirim backend:

```powershell
mosquitto_sub -h 127.0.0.1 -t "rice-nursery/ESP32-01/actuator/fan/#" -v
```

Lalu dari Flutter tekan kontrol kipas ON/OFF.

Jika berhasil, terminal subscriber akan menerima pesan pada topic:

```text
rice-nursery/ESP32-01/actuator/fan/command
rice-nursery/ESP32-01/actuator/fan/mode
```

Contoh payload command:

```json
{
  "device_id": "ESP32-01",
  "command": "ON",
  "timestamp": "2026-09-26T00:00:00+07:00"
}
```

Contoh payload mode:

```json
{
  "device_id": "ESP32-01",
  "mode": "MANUAL",
  "timestamp": "2026-09-26T00:00:00+07:00"
}
```

## 6. Simulasi Manual Dengan Mosquitto Pub

Selain command Laravel, payload juga bisa dikirim manual.

Heartbeat device:

```powershell
mosquitto_pub -h 127.0.0.1 -t "rice-nursery/ESP32-01/status" -m "{\"status\":\"ONLINE\"}"
```

Sensor normal:

```powershell
mosquitto_pub -h 127.0.0.1 -t "rice-nursery/ESP32-01/sensors" -m "{\"temperature\":27.2,\"humidity\":76,\"nitrogen\":45,\"phosphorus\":25,\"potassium\":35,\"light\":650,\"ultrasonic\":18}"
```

Sensor warning:

```powershell
mosquitto_pub -h 127.0.0.1 -t "rice-nursery/ESP32-01/sensors" -m "{\"temperature\":32.4,\"humidity\":88,\"nitrogen\":26,\"phosphorus\":25,\"potassium\":35,\"light\":650,\"ultrasonic\":18}"
```

Sensor critical:

```powershell
mosquitto_pub -h 127.0.0.1 -t "rice-nursery/ESP32-01/sensors" -m "{\"temperature\":34.8,\"humidity\":91,\"nitrogen\":23,\"phosphorus\":12,\"potassium\":18,\"light\":1100,\"ultrasonic\":3}"
```

Feedback status kipas:

```powershell
mosquitto_pub -h 127.0.0.1 -t "rice-nursery/ESP32-01/actuator/fan/status" -m "{\"status\":\"ON\"}"
```

## Troubleshooting

Jika Flutter tidak berubah:

```powershell
php artisan mqtt:listen
```

Pastikan listener masih hidup dan menerima payload.

Jika listener tidak menerima payload, cek broker:

```powershell
mosquitto_sub -h 127.0.0.1 -t "rice-nursery/#" -v
```

Jika command kipas dari Flutter tidak muncul, pastikan API backend sedang berjalan dan user Flutter punya role `operator` untuk endpoint:

```text
POST /api/actuators/fan/control
```

Jika Mosquitto menolak koneksi, cek `MQTT_HOST`, `MQTT_PORT`, username, dan password di `.env`.
