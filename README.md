# Oryza Backend

Oryza Backend adalah REST API Laravel untuk monitoring pembibitan padi berbasis ESP32. Backend menerima heartbeat, pembacaan sensor, dan feedback kipas melalui MQTT; menyimpan datanya ke MySQL; lalu menyediakannya untuk aplikasi Flutter melalui API yang dilindungi Laravel Sanctum.

Project ini tidak lagi memakai perangkat kamera atau API detection. Fitur Oryza Vision yang tersisa adalah prediksi dari gambar yang diunggah aplikasi dan sinkronisasi riwayat scan manual.

## Fitur

- Autentikasi API dengan Laravel Sanctum.
- Status koneksi ESP32 berdasarkan heartbeat MQTT.
- Monitoring suhu, kelembapan, NPK, cahaya, UV, jarak sensor ultrasonik, dan persentase ketinggian air.
- Status aktual dan kontrol kipas `ON`, `OFF`, atau `AUTO`.
- Ambang sensor yang dapat diubah operator.
- Alert dan rekomendasi otomatis ketika pembacaan melewati ambang.
- Prediksi penyakit/hama dari gambar melalui Oryza Vision API.
- Simulator ESP32 dan MQTT untuk development.

## Stack

- PHP 8.4
- Laravel 13
- Laravel Sanctum 4
- MySQL
- `php-mqtt/client`
- PHPUnit 12

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Jalankan API dan listener MQTT pada terminal terpisah:

```bash
php artisan serve
```

```bash
php artisan mqtt:subscribe
```

Alias berikut juga tersedia:

```bash
php artisan mqtt:listen
```

Listener MQTT harus tetap aktif agar heartbeat dan data ESP32 diproses.

## Environment

Konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padibackend
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi broker MQTT publik untuk development:

```env
MQTT_HOST=broker.hivemq.com
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_oryza_smart
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_DEFAULT_DEVICE_ID=ESP32-01
MQTT_SENSOR_TOPIC=oryza-smart/ESP32-01/sensors
MQTT_STATUS_TOPIC=oryza-smart/ESP32-01/status
MQTT_FAN_STATUS_TOPIC=oryza-smart/ESP32-01/actuator/fan/status
MQTT_FAN_COMMAND_TOPIC=oryza-smart/ESP32-01/actuator/fan/command
MQTT_FAN_COMMAND_PAYLOAD=wokwi_json
MQTT_LAST_WILL_TOPIC=oryza-smart/backend/status
```

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

Broker publik cocok untuk development, tetapi tidak menyediakan kepemilikan topic. Production sebaiknya memakai broker privat dengan autentikasi, ACL topic, dan TLS.

Konfigurasi Oryza Vision untuk upload gambar dari Flutter:

```env
ORYZA_VISION_URL=http://127.0.0.1:8000
ORYZA_VISION_API_KEY=
```

## Kontrak MQTT ESP32

ESP32 mengirim heartbeat ke:

```text
oryza-smart/ESP32-01/status
```

Payload online dan offline:

```json
{"status":"ON"}
```

```json
{"status":"OFF"}
```

Nilai `ON/OFF` dinormalisasi backend menjadi `ONLINE/OFFLINE`. Nilai tersebut juga boleh dikirim langsung sebagai `ONLINE/OFFLINE`.

ESP32 mengirim pembacaan sensor ke:

```text
oryza-smart/ESP32-01/sensors
```

```json
{
  "device_id": "ESP32-01",
  "uptime_s": 120,
  "temperature": 28.5,
  "humidity": 74,
  "npk": {"n": 45, "p": 25, "k": 35},
  "light": {"percent": 65, "uv_index": 3.2},
  "ultrasonic": {"distance_cm": 18, "level_percent": 72},
  "fan": {"state": "OFF", "mode": "AUTO"}
}
```

ESP32 menerima command kipas dari:

```text
oryza-smart/ESP32-01/actuator/fan/command
```

Command manual:

```json
{"state":"ON","mode":"MANUAL"}
```

Command otomatis:

```json
{"mode":"AUTO"}
```

Setelah menjalankan command, ESP32 mengirim feedback aktual ke:

```text
oryza-smart/ESP32-01/actuator/fan/status
```

```json
{"state":"ON","mode":"AUTO"}
```

## Endpoint Utama

Endpoint publik:

```text
POST /api/login
```

Endpoint berikut memerlukan bearer token Sanctum:

```text
POST /api/logout
GET  /api/user
GET  /api/dashboard
GET  /api/devices
GET  /api/devices/{id}
GET  /api/devices/{id}/status
GET  /api/sensors/latest
GET  /api/sensors/history
GET  /api/thresholds
PUT  /api/thresholds/{sensorType}
GET  /api/actuators/fan/status
POST /api/actuators/fan/control
GET  /api/actuators/logs
GET  /api/alerts
GET  /api/alerts/{id}
POST /api/alerts/{id}/read
GET  /api/recommendations
GET  /api/recommendations/latest
GET  /api/recommendations/{id}
POST /api/paddy/predict
POST /api/paddy/predict/realtime
GET  /api/vision/scans
POST /api/vision/scans
GET  /api/vision/scans/{visionScan}
```

Route `/api/cameras`, `/api/detections`, dan `/api/vision/detections` sudah tidak tersedia.

Kontrak request dan response lengkap tersedia di [`docs/API_ENDPOINTS_FLUTTER.md`](docs/API_ENDPOINTS_FLUTTER.md).

## Simulator

Simulasi data sensor langsung ke database:

```bash
php artisan nursery:simulate --count=10 --scenario=mixed
```

Simulasi ESP32 melalui broker MQTT:

```bash
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=mixed --count=10 --interval=1
```

Tambahkan feedback kipas bila diperlukan:

```bash
php artisan mqtt:simulate-device --device=ESP32-01 --scenario=critical --count=5 --fan-status=ON
```

Scenario yang tersedia: `normal`, `warning`, `critical`, dan `mixed`.

## Testing dan Formatting

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

## Deployment

Isi secret production melalui environment platform, lalu jalankan:

```bash
php artisan migrate --force
php artisan config:cache
```

Jalankan `php artisan mqtt:subscribe` sebagai proses background yang dipantau process manager. Laravel scheduler juga harus dijalankan agar perangkat tanpa heartbeat ditandai offline.
