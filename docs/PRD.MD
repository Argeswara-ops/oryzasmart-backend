# PRODUCT REQUIREMENTS DOCUMENT

## Backend Laravel — Smart Rice Nursery

**Versi:** MVP 1.0
**Backend:** Laravel
**Database:** MySQL
**IoT Protocol:** MQTT
**Mobile Client:** Flutter Android & iOS
**Vision Service:** Python + OpenCV + YOLO
**Embedded:** ESP32

---

# 1. Overview

Smart Rice Nursery Backend merupakan backend utama sistem pemantauan pembibitan padi yang mengintegrasikan:

* Aplikasi Flutter
* ESP32
* Sensor IoT
* MQTT Broker
* Kamera/CCTV/Webcam
* Computer Vision
* Sistem Cerdas
* Database MySQL
* Aktuator kipas

Laravel bertanggung jawab sebagai **central backend** untuk menerima, memproses, menyimpan, dan menyediakan data kepada aplikasi Flutter.

---

# 2. Tujuan Backend

Backend harus mampu:

1. Menyediakan REST API untuk Flutter.
2. Mengelola autentikasi pengguna.
3. Terintegrasi dengan MQTT Broker.
4. Menerima data sensor dari ESP32.
5. Menyimpan histori sensor.
6. Menerima hasil computer vision.
7. Menyimpan hasil deteksi hama dan penyakit.
8. Mengelola status perangkat.
9. Menjalankan aturan sistem cerdas.
10. Menghasilkan alert.
11. Menghasilkan rekomendasi.
12. Mengirim command aktuator melalui MQTT.
13. Menyimpan histori kontrol aktuator.
14. Menyediakan data dashboard.

---

# 3. Arsitektur Backend

```text
                  FLUTTER
                     │
                  REST API
                     │
                     ▼
                ┌─────────┐
                │ LARAVEL │
                └────┬────┘
                     │
       ┌─────────────┼──────────────┐
       │             │              │
       ▼             ▼              ▼
     MySQL      MQTT Service    Vision API
                     │              ▲
                     ▼              │
                MQTT Broker      Python
                     │          YOLO/OpenCV
                     ▼              ▲
                   ESP32          Camera
                     │
              ┌──────┴──────┐
              ▼             ▼
           Sensors        Actuator
                            Kipas
```

---

# 4. Scope Laravel

Laravel menangani:

### API Layer

REST API untuk komunikasi dengan Flutter dan Vision Service.

### IoT Layer

Integrasi Laravel dengan MQTT Broker.

### Data Layer

Penyimpanan seluruh data aplikasi ke MySQL.

### Smart System Layer

Analisis data sensor dan hasil computer vision berdasarkan rules yang ditentukan.

### Notification Layer

Pembuatan alert ketika kondisi abnormal terjadi.

### Device Control Layer

Mengirim command melalui MQTT kepada ESP32.

---

# 5. User & Authentication

MVP memiliki role:

### Operator

Operator dapat:

* Login
* Logout
* Melihat dashboard
* Melihat sensor
* Melihat kamera
* Melihat hasil vision
* Melihat histori
* Melihat alert
* Melihat rekomendasi
* Mengontrol kipas
* Mengubah mode kipas
* Melihat status perangkat

Authentication menggunakan:

**Laravel Sanctum**

Endpoint:

```text
POST /api/login
POST /api/logout
GET  /api/user
```

---

# 6. Device Management

Laravel menyimpan seluruh perangkat IoT.

Contoh perangkat:

```text
ESP32-01
CAM-01
FAN-01
```

Data device:

* Device ID
* Device Code
* Nama
* Tipe
* Lokasi
* Status
* Last Seen
* Created At

Status:

```text
ONLINE
OFFLINE
ERROR
```

API:

```text
GET /api/devices
GET /api/devices/{id}
GET /api/devices/{id}/status
```

---

# 7. MQTT Integration

Laravel harus terhubung dengan MQTT Broker.

MQTT digunakan untuk:

### Subscribe

Laravel menerima:

* Data sensor
* Device heartbeat
* Status aktuator

### Publish

Laravel mengirim:

* Command kipas
* Mode aktuator

Struktur topic:

```text
rice-nursery/{device_id}/sensors
rice-nursery/{device_id}/status

rice-nursery/{device_id}/actuator/fan/status
rice-nursery/{device_id}/actuator/fan/command
rice-nursery/{device_id}/actuator/fan/mode
```

---

# 8. Sensor Data

Sensor yang digunakan:

* Suhu ruangan
* Kelembapan udara
* Nitrogen
* Phosphorus
* Potassium
* Cahaya / UV
* Ultrasonik

ESP32 mengirim:

```json
{
  "device_id": "ESP32-01",
  "temperature": 29.4,
  "humidity": 81,
  "nitrogen": 40,
  "phosphorus": 22,
  "potassium": 31,
  "light": 720,
  "ultrasonic": 14
}
```

Flow:

```text
Sensor
  ↓
ESP32
  ↓
MQTT Broker
  ↓
Laravel MQTT Consumer
  ↓
Validation
  ↓
Sensor Service
  ↓
MySQL
```

---

# 9. Sensor Validation

Laravel melakukan validasi terhadap payload.

Validasi minimum:

* Device terdaftar
* Payload valid
* Nilai berupa angka
* Timestamp valid
* Tidak terdapat field sensor wajib yang hilang

Data valid disimpan.

Data invalid dapat dicatat pada application log.

---

# 10. Sensor History

Laravel menyediakan data histori untuk Flutter.

Endpoint:

```text
GET /api/sensors/latest

GET /api/sensors/history

GET /api/sensors/history?range=today

GET /api/sensors/history?range=7days

GET /api/sensors/history?range=30days
```

Response dapat menyediakan:

* Current
* Average
* Minimum
* Maximum
* Time series

Contoh:

```json
{
  "sensor": "temperature",
  "current": 29.4,
  "average": 28.7,
  "minimum": 26.8,
  "maximum": 31.2
}
```

---

# 11. Computer Vision Integration

Computer Vision merupakan service terpisah.

Stack:

```text
Python
OpenCV
YOLO
PyTorch / Ultralytics
```

Laravel tidak melakukan inference YOLO.

Flow:

```text
Camera
   ↓
Python
   ↓
OpenCV
   ↓
YOLO
   ↓
Detection
   ↓
Laravel REST API
   ↓
MySQL
```

---

# 12. Vision API

Python mengirim hasil detection ke:

```text
POST /api/vision/detections
```

Payload:

```json
{
  "camera_id": "CAM-01",
  "detection_type": "disease",
  "class_name": "rice_blast",
  "confidence": 0.92,
  "image_path": "detections/cam01-001.jpg",
  "detected_at": "2026-09-24 11:40:21"
}
```

Detection type:

```text
PEST
DISEASE
HEALTHY
UNKNOWN
```

---

# 13. Detection Management

Laravel menyimpan:

* Camera
* Detection type
* Class
* Confidence
* Detection image
* Detection time

API Flutter:

```text
GET /api/detections
GET /api/detections/latest
GET /api/detections/{id}
```

Filter:

```text
GET /api/detections?type=pest
GET /api/detections?type=disease
GET /api/detections?camera=CAM-01
```

---

# 14. Camera Management

Data kamera:

* Camera ID
* Nama
* Lokasi
* Stream URL
* Status
* Last Seen

Status:

```text
ONLINE
OFFLINE
ERROR
```

API:

```text
GET /api/cameras
GET /api/cameras/{id}
GET /api/cameras/{id}/status
```

Laravel tidak melakukan video processing.

---

# 15. Sistem Cerdas

Sistem cerdas menggunakan dua sumber utama:

```text
DATA SENSOR
     +
VISION RESULT
     │
     ▼
SMART DECISION SERVICE
     │
     ├── NORMAL
     ├── WARNING
     └── CRITICAL
```

Sistem dapat menggunakan rule/threshold yang telah ditentukan berdasarkan kebutuhan penelitian.

Contoh:

```text
IF humidity > threshold
AND disease = rice_blast
THEN

status = WARNING / CRITICAL

Generate Alert

Generate Recommendation
```

Nilai threshold tidak di-hardcode pada aplikasi Flutter.

---

# 16. Smart Decision Service

Buat service Laravel khusus:

```text
SmartDecisionService
```

Tanggung jawab:

* Membaca sensor terbaru.
* Membaca detection terbaru.
* Mengevaluasi rule.
* Menentukan status.
* Membuat alert.
* Membuat recommendation.

Flow:

```text
Sensor Reading ─────┐
                    │
                    ▼
             Smart Decision
                    ▲
                    │
Vision Detection ───┘
                    │
                    ▼
               Evaluation
                    │
          ┌─────────┼─────────┐
          ▼         ▼         ▼
       NORMAL    WARNING   CRITICAL
                    │         │
                    ▼         ▼
                  Alert   Alert + Action
                    │
                    ▼
             Recommendation
```

---

# 17. Threshold Management

Threshold sebaiknya disimpan di database.

Contoh:

```text
temperature_min
temperature_max

humidity_min
humidity_max

nitrogen_min
nitrogen_max

phosphorus_min
phosphorus_max

potassium_min
potassium_max

light_min
light_max

ultrasonic_min
ultrasonic_max
```

Dengan demikian threshold dapat diubah tanpa mengubah source code.

---

# 18. Alert System

Alert dibuat ketika terjadi:

* Suhu abnormal
* Kelembapan abnormal
* NPK abnormal
* Cahaya/UV abnormal
* Level air abnormal
* Hama terdeteksi
* Penyakit terdeteksi
* ESP32 offline
* Kamera offline

Severity:

```text
INFO
WARNING
CRITICAL
```

Data:

* Type
* Source
* Severity
* Title
* Message
* Status
* Timestamp

API:

```text
GET /api/alerts

GET /api/alerts/{id}

POST /api/alerts/{id}/read
```

---

# 19. Recommendation System

Recommendation dibuat berdasarkan hasil Smart Decision Service.

Contoh:

```text
Status:
WARNING

Source:
Vision + Humidity

Condition:
Rice Blast terdeteksi dengan confidence 92%.
Kelembapan udara berada di atas threshold.

Recommendation:
Lakukan pemeriksaan langsung pada tanaman
di area CAM-01 dan periksa kondisi
lingkungan pembibitan.
```

API:

```text
GET /api/recommendations
GET /api/recommendations/latest
GET /api/recommendations/{id}
```

---

# 20. Actuator Management

MVP memiliki aktuator:

**Kipas**

Mode:

```text
AUTO
MANUAL
```

Status:

```text
ON
OFF
OFFLINE
```

---

# 21. Manual Control

Flow:

```text
Flutter
   ↓
Laravel API
   ↓
Authorization
   ↓
Actuator Service
   ↓
MQTT Publish
   ↓
MQTT Broker
   ↓
ESP32
   ↓
Relay
   ↓
Kipas
```

Endpoint:

```text
POST /api/actuators/fan/control
```

Request:

```json
{
  "device_id": "ESP32-01",
  "command": "ON"
}
```

---

# 22. Automatic Control

Pada mode AUTO:

```text
Sensor
   ↓
Laravel
   ↓
Smart Decision
   ↓
Rule Evaluation
   ↓
Actuator Service
   ↓
MQTT
   ↓
ESP32
   ↓
Kipas
```

Contoh:

```text
IF temperature >= configured_threshold
THEN fan = ON

IF temperature kembali normal
THEN fan = OFF
```

Aturan final harus mengikuti kebutuhan sistem/pakar penelitian.

---

# 23. Actuator Logs

Setiap tindakan disimpan.

Data:

* Actuator
* Command
* Mode
* Source
* User
* Previous status
* New status
* Timestamp

Source:

```text
USER
SYSTEM
```

Dengan demikian dapat diketahui apakah kipas dinyalakan pengguna atau sistem otomatis.

---

# 24. Dashboard API

Flutter sebaiknya tidak melakukan banyak request ketika dashboard pertama dibuka.

Sediakan:

```text
GET /api/dashboard
```

Response:

```json
{
  "system_status": "WARNING",
  "device_status": "ONLINE",

  "sensors": {
    "temperature": 29.4,
    "humidity": 81,
    "nitrogen": 40,
    "phosphorus": 22,
    "potassium": 31,
    "light": 720,
    "ultrasonic": 14
  },

  "latest_detection": {
    "type": "DISEASE",
    "class": "rice_blast",
    "confidence": 0.92
  },

  "fan": {
    "status": "ON",
    "mode": "AUTO"
  },

  "unread_alerts": 2
}
```

---

# 25. Database

Tabel utama:

```text
users

devices

sensor_readings

sensor_thresholds

cameras

detections

actuators

actuator_logs

alerts

recommendations

personal_access_tokens
```

---

# 26. Database Relationship

```text
devices
   │
   ├── sensor_readings
   │
   ├── actuators
   │       │
   │       └── actuator_logs
   │
   └── alerts

cameras
   │
   └── detections
           │
           └── alerts

alerts
   │
   └── recommendations

users
   │
   └── actuator_logs
```

---

# 27. Struktur Laravel

Struktur logika bisnis disarankan:

```text
app/
│
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── SensorController.php
│   │       ├── DeviceController.php
│   │       ├── CameraController.php
│   │       ├── DetectionController.php
│   │       ├── AlertController.php
│   │       ├── RecommendationController.php
│   │       └── ActuatorController.php
│   │
│   └── Requests/
│
├── Models/
│
├── Services/
│   ├── MqttService.php
│   ├── SensorService.php
│   ├── VisionService.php
│   ├── SmartDecisionService.php
│   ├── AlertService.php
│   └── ActuatorService.php
│
├── Jobs/
│
├── Events/
│
├── Listeners/
│
└── Console/
    └── Commands/
```

Controller tidak boleh berisi logika bisnis kompleks.

Gunakan:

```text
Controller
    ↓
Service
    ↓
Model / MQTT / External Service
```

---

# 28. API Structure

Gunakan prefix:

```text
/api
```

Struktur:

```text
/api/auth/*

/api/dashboard

/api/devices/*
/api/sensors/*
/api/cameras/*
/api/detections/*
/api/alerts/*
/api/recommendations/*
/api/actuators/*
```

---

# 29. MQTT Consumer

Laravel harus memiliki proses background yang terus melakukan subscribe ke MQTT.

Contoh konsep command:

```text
php artisan mqtt:listen
```

Consumer subscribe ke:

```text
rice-nursery/+/sensors
rice-nursery/+/status
rice-nursery/+/actuator/+/status
```

Kemudian:

```text
MQTT Message
     ↓
Parse Payload
     ↓
Validate Device
     ↓
Process Message
     ↓
Store Database
     ↓
Evaluate Smart Rules
```

MQTT consumer dijalankan sebagai long-running process di server, bukan melalui request HTTP biasa.

---

# 30. Queue & Background Processing

Gunakan Laravel Queue untuk proses yang tidak harus selesai dalam HTTP request.

Contoh:

* Process sensor reading
* Evaluate smart rules
* Generate alert
* Process vision result
* Notification
* Device offline checking

Flow:

```text
MQTT / API
    ↓
Laravel
    ↓
Dispatch Job
    ↓
Queue
    ↓
Background Worker
```

---

# 31. Device Heartbeat

ESP32 mengirim heartbeat secara berkala:

```text
rice-nursery/ESP32-01/status
```

Payload:

```json
{
  "status": "ONLINE"
}
```

Laravel memperbarui:

```text
last_seen
```

Jika perangkat tidak mengirim heartbeat dalam periode yang ditentukan:

```text
ONLINE
  ↓
timeout
  ↓
OFFLINE
  ↓
Create Alert
```

Konsep serupa diterapkan pada kamera/Vision Service.

---

# 32. Security

Minimum security:

* Laravel Sanctum
* Authentication middleware
* Authorization
* API validation
* Rate limiting
* MQTT authentication
* MQTT username/password
* Protected Vision API
* Environment variables untuk credentials
* Password hashing
* File upload validation

Python Vision Service tidak boleh bebas mengakses endpoint internal tanpa authentication.

---

# 33. Error Handling

Format response konsisten.

Success:

```json
{
  "success": true,
  "message": "Sensor data retrieved",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Device not found",
  "errors": {}
}
```

HTTP status mengikuti kondisi:

```text
200 OK
201 Created
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
422 Validation Error
500 Internal Server Error
```

---

# 34. Logging

Backend mencatat:

* Login
* MQTT connection error
* Invalid MQTT payload
* Device offline
* Vision API error
* Actuator command
* Failed actuator command
* System exception

Jangan menyimpan password/token dalam log.

---

# 35. MVP Backend

Backend MVP wajib memiliki:

* Laravel REST API
* MySQL
* Laravel Sanctum
* Authentication
* Device management
* Sensor management
* MQTT connection
* MQTT consumer
* Sensor history
* Camera management
* Vision integration API
* Detection history
* Smart Decision Service
* Threshold management
* Alert system
* Recommendation
* Kipas AUTO/MANUAL
* MQTT actuator command
* Actuator logs
* Dashboard API
* Device heartbeat
* Basic logging
* API validation

---

# 36. Alur Utama Sistem

```text
========================
        IoT DATA
========================

Sensor
   ↓
ESP32
   ↓
MQTT Broker
   ↓
Laravel MQTT Consumer
   ↓
MySQL
   ↓
Smart Decision


========================
     COMPUTER VISION
========================

Camera
   ↓
Python + OpenCV
   ↓
YOLO
   ↓
Laravel API
   ↓
MySQL
   ↓
Smart Decision


========================
       MOBILE APP
========================

Flutter
   ↓
Laravel REST API
   ↓
MySQL
   ↓
Sensor + Vision + Alert
   ↓
Flutter


========================
    ACTUATOR CONTROL
========================

Flutter / Smart Decision
          ↓
        Laravel
          ↓
         MQTT
          ↓
        ESP32
          ↓
         Kipas
```

---

# 37. Kriteria Keberhasilan Backend

Backend dianggap berhasil apabila:

1. Laravel dapat menerima data sensor melalui MQTT.
2. Data sensor tersimpan ke MySQL.
3. Flutter dapat mengambil data melalui REST API.
4. Histori sensor dapat difilter berdasarkan waktu.
5. Python Vision Service dapat mengirim hasil YOLO ke Laravel.
6. Detection dapat disimpan dan ditampilkan kembali.
7. Sistem dapat menghasilkan alert dari kondisi abnormal.
8. Sistem dapat menghasilkan rekomendasi.
9. Flutter dapat mengirim perintah kipas.
10. Laravel dapat meneruskan perintah tersebut melalui MQTT.
11. ESP32 dapat mengirim status aktual kipas kembali.
12. Mode AUTO dan MANUAL berjalan.
13. Status perangkat online/offline dapat diketahui.
14. Seluruh aktivitas penting memiliki histori.

---

# 38. Stack Final Backend

```text
BACKEND
├── Laravel
├── Laravel Sanctum
├── REST API
├── Laravel Queue
└── MySQL

IoT
├── MQTT Broker
├── Laravel MQTT Consumer
├── ESP32
└── Sensors / Actuator

VISION
├── Python
├── OpenCV
├── YOLO
└── PyTorch / Ultralytics

CLIENT
└── Flutter
    ├── Android
    └── iOS
```

## Prinsip Utama

Laravel adalah **pusat sistem**, bukan engine computer vision dan bukan pengganti MQTT.

```text
ESP32 ── MQTT ──► Laravel ◄── REST ── Python/YOLO
                    ▲
                    │
                 REST API
                    │
                    ▼
                  Flutter
```

Dengan pembagian ini, setiap bagian dapat dikembangkan secara independen tanpa membuat Flutter, IoT, dan Computer Vision saling bergantung secara langsung.
