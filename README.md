# Oryza Backend

Oryza Backend adalah REST API Laravel untuk sistem monitoring dan kontrol pembibitan padi. Backend ini menghubungkan aplikasi Flutter, perangkat IoT berbasis MQTT, dan layanan Computer Vision untuk memantau sensor, kamera, deteksi hama/penyakit, alert, rekomendasi, serta kontrol aktuator.

## Fitur Utama

- Autentikasi API menggunakan Laravel Sanctum.
- Dashboard kondisi pembibitan berdasarkan sensor, kamera, alert, rekomendasi, dan status aktuator.
- Ingestion data sensor dari perangkat IoT/MQTT.
- Integrasi MQTT untuk status perangkat dan kontrol kipas.
- Endpoint callback Vision Service untuk menyimpan hasil deteksi.
- Endpoint prediksi padi yang meneruskan upload gambar dari Flutter ke Oryza Vision API.
- Alert dan rekomendasi otomatis melalui `SmartDecisionService`.
- Simulator data sensor, deteksi vision, dan MQTT untuk development tanpa perangkat fisik.

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum
- MySQL
- PHPUnit 12
- php-mqtt/client

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Jalankan server lokal:

```bash
php artisan serve
```

## Konfigurasi Environment

Konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padibackend
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi MQTT:

```env
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_padibackend
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

Konfigurasi Vision Service:

```env
VISION_API_KEY=
ORYZA_VISION_URL=http://127.0.0.1:8000
ORYZA_VISION_API_KEY=
```

`VISION_API_KEY` dipakai oleh endpoint callback Vision Service ke Laravel. `ORYZA_VISION_URL` dan `ORYZA_VISION_API_KEY` dipakai saat Laravel meneruskan gambar dari Flutter ke Oryza Vision API.

## Endpoint Utama

Endpoint publik:

```text
POST /api/login
POST /api/vision/detections
```

Endpoint authenticated:

```text
POST /api/logout
GET  /api/user
GET  /api/dashboard
GET  /api/devices
GET  /api/sensors/latest
GET  /api/sensors/history
GET  /api/cameras
GET  /api/detections
GET  /api/alerts
GET  /api/recommendations
POST /api/paddy/predict
POST /api/paddy/predict/realtime
POST /api/actuators/fan/control
GET  /api/actuators/fan/status
```

Dokumentasi kontrak Flutter tersedia di `docs/API_ENDPOINTS_FLUTTER.md`. Panduan integrasi Oryza Vision tersedia di `docs/laravel-integration.md`.

## Integrasi Oryza Vision

Flutter mengirim gambar ke Laravel:

```text
POST /api/paddy/predict
POST /api/paddy/predict/realtime
```

Laravel memvalidasi field `image`, lalu meneruskan file ke Oryza Vision API sebagai multipart field `file` dengan header `X-API-Key`.

Vision Service juga dapat mengirim hasil deteksi langsung ke Laravel:

```text
POST /api/vision/detections
Header: X-Vision-Key: {VISION_API_KEY}
```

Payload deteksi:

```json
{
  "camera_id": "CAM-01",
  "detection_type": "DISEASE",
  "class_name": "rice_blast",
  "confidence": 0.92,
  "image_path": "detections/cam01-001.jpg",
  "detected_at": "2026-09-24 11:40:21"
}
```

## MQTT

Listener MQTT:

```bash
php artisan mqtt:listen
```

Simulator MQTT:

```bash
php artisan mqtt:simulate --count=5
```

Simulator data nursery:

```bash
php artisan nursery:simulate --sensor --detection --count=5 --scenario=mixed
```

## Testing dan Formatting

Jalankan seluruh test:

```bash
php artisan test --compact
```

Jalankan test tertentu:

```bash
php artisan test tests/Feature/SmartRiceNurseryApiTest.php --compact
```

Format kode PHP:

```bash
vendor/bin/pint --dirty --format agent
```

## Deployment

Pastikan environment production mengisi secret berikut:

```env
APP_KEY=
DB_*
VISION_API_KEY=
ORYZA_VISION_URL=
ORYZA_VISION_API_KEY=
MQTT_*
```

Jalankan migration pada environment target:

```bash
php artisan migrate --force
```
