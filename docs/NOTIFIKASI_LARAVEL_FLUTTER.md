# Konfigurasi Notifikasi Laravel dan Flutter

Dokumen ini menjelaskan konfigurasi Firebase Cloud Messaging (FCM) agar Laravel
dapat mengirim push notification ke aplikasi Flutter.

## Alur

1. Flutter meminta izin notifikasi dan mengambil FCM registration token perangkat.
2. Flutter mengirim token ke Laravel menggunakan bearer token Sanctum.
3. Laravel menyimpan token pada kolom `users.fcm_token`.
4. Laravel mengirim notifikasi ke perangkat melalui Firebase Cloud Messaging HTTP v1.

FCM token berbeda dari token autentikasi Sanctum. Sanctum mengautentikasi request
API; FCM token menentukan perangkat tujuan notifikasi.

## Persiapan Firebase

1. Buat atau pilih Firebase project.
2. Tambahkan aplikasi Android dan/atau iOS ke Firebase project yang sama dengan
   yang akan digunakan backend.
3. Aktifkan **Firebase Cloud Messaging API** pada Google Cloud project terkait.
4. Unduh konfigurasi aplikasi klien melalui Firebase Console atau FlutterFire CLI:
   - Android: `google-services.json`
   - iOS: `GoogleService-Info.plist`
5. Buat atau pilih service account dengan izin yang diperlukan untuk mengirim
   pesan Firebase Cloud Messaging HTTP v1. Unduh JSON service account untuk backend.

Jangan memasukkan file service account ke aplikasi Flutter, repository, atau
riwayat Git. File tersebut berisi private key dan hanya boleh tersedia secara
aman di server Laravel.

## Konfigurasi Laravel

### Pasang kredensial

Letakkan file JSON service account pada:

```text
storage/app/firebase-credentials.json
```

Pastikan file dapat dibaca oleh proses PHP dan properti `client_email`,
`private_key`, serta `project_id` tersedia. `project_id` pada file harus cocok
dengan Firebase project tempat aplikasi Flutter didaftarkan.

Di backend ini, Firebase HTTP v1 dan OAuth access token ditangani oleh
`FirebaseService`; tidak perlu menaruh service account atau private key di
`.env` atau mengirimkannya ke Flutter.

### Migrasikan database

Kolom FCM token disimpan di tabel `users`. Jalankan migrasi pada environment
Laravel yang digunakan:

```bash
php artisan migrate
```

Jangan menghapus atau mengubah database produksi hanya untuk menguji notifikasi.

### Jalankan API untuk perangkat lokal

Jalankan server agar bisa diakses dari perangkat uji:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Pilih base URL yang sesuai pada aplikasi Flutter:

| Target Flutter | Base URL API |
| --- | --- |
| Android emulator | `http://10.0.2.2:8000/api` |
| iOS Simulator | `http://127.0.0.1:8000/api` |
| Perangkat fisik | `http://<IP-LAN-komputer>:8000/api` |

Komputer dan perangkat fisik harus berada pada jaringan yang dapat saling
mengakses. Untuk production, gunakan HTTPS.

## Konfigurasi Flutter

### Firebase dan izin notifikasi

Pasang dan konfigurasi `firebase_core` serta `firebase_messaging` mengikuti
versi Flutter dan panduan FlutterFire proyek. Inisialisasikan Firebase sebelum
mengakses FCM:

```dart
await Firebase.initializeApp();

final settings = await FirebaseMessaging.instance.requestPermission();
final messaging = FirebaseMessaging.instance;
final fcmToken = await messaging.getToken();
```

Tangani kondisi `fcmToken == null`, misalnya dengan mencoba lagi setelah
konfigurasi Firebase dan konektivitas perangkat siap. Untuk Android 13 atau
yang lebih baru, pengguna harus memberikan izin notifikasi. Untuk iOS,
konfigurasikan APNs pada Firebase project dan minta izin dari pengguna.

Tambahkan listener `FirebaseMessaging.onMessage` untuk pesan saat aplikasi
aktif. Gunakan `message.notification?.title` dan
`message.notification?.body` untuk menampilkan local notification atau
memperbarui UI. Pada iOS, atur juga foreground presentation options jika
notifikasi harus ditampilkan saat aplikasi aktif.

FCM tidak otomatis menampilkan notifikasi pada notification tray Android ketika
aplikasi sedang foreground. Jika notifikasi harus terlihat saat itu, integrasikan
local notifications dan gunakan channel Android dengan ID:

```text
high_importance_channel
```

### Daftarkan token ke Laravel

Setelah login berhasil, kirim FCM token perangkat ke endpoint berikut:

```http
POST /api/user/fcm-token
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "fcm_token": "<FCM_TOKEN_DARI_PERANGKAT>"
}
```

Laravel menyimpan token untuk user yang terautentikasi. Perbarui token ke
endpoint yang sama setiap kali token dibuat atau berubah:

```dart
FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
  // Kirim newToken ke POST /api/user/fcm-token menggunakan Sanctum token aktif.
});
```

Pastikan callback refresh hanya mengirim token ketika sesi pengguna memiliki
Sanctum token yang valid. Jika token diperbarui ketika pengguna belum login,
simpan sementara dan daftarkan setelah login.

> **Batasan saat ini:** backend menyimpan satu `fcm_token` per user. Login dari
> perangkat lain akan menggantikan token yang tersimpan sebelumnya. Untuk logout
> atau mematikan notifikasi pada perangkat ini, hapus token melalui
> `DELETE /api/user/fcm-token` dengan bearer token yang sama.

## Tes pengiriman

Login melalui `POST /api/login`, lalu gunakan `data.token` sebagai
`<SANCTUM_TOKEN>`. Setelah token Flutter terdaftar, kirim:

```http
POST /api/notifications/test
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```

Body dapat dikosongkan untuk menggunakan pesan tes default, atau berisi:

```json
{
  "title": "Tes PadiApps",
  "body": "Notifikasi dari Laravel berhasil."
}
```

Endpoint tes mengirim hanya ke token yang tersimpan untuk user yang sedang
login. Respons `success: true` berarti FCM menerima pesan untuk diproses; tetap
periksa aplikasi dan status perangkat untuk memastikan notifikasi terlihat.

## Notifikasi alert otomatis

Ketika backend membuat alert perangkat atau kamera offline baru, Laravel
mencoba mengirim push notification ke user yang memiliki FCM token. Pengiriman
otomatis ini menggunakan broadcast ke token-token yang terdaftar, sedangkan
endpoint tes hanya mengirim ke token user yang login.

## Troubleshooting

| Gejala | Yang perlu diperiksa |
| --- | --- |
| `401 Unauthorized` | Header `Authorization: Bearer ...` berisi Sanctum token yang valid. |
| `422` dan token tidak ditemukan | Login di Flutter, ambil FCM token, lalu panggil `POST /api/user/fcm-token`. |
| Gagal memperoleh OAuth access token | Pastikan file service account valid, dapat dibaca, private key benar, dan FCM HTTP v1 API aktif. |
| FCM menolak request | Pastikan token perangkat berasal dari Firebase project yang sama dengan service account dan periksa log Laravel. |
| Notifikasi tidak terlihat saat aplikasi terbuka | Tambahkan foreground listener dan local-notification handling pada Flutter. |
| Tidak ada notifikasi pada iOS | Periksa izin pengguna, konfigurasi APNs, dan pengaturan Firebase iOS. |
| API tidak dapat diakses dari HP fisik | Gunakan IP LAN komputer, pastikan satu jaringan, dan izinkan koneksi pada firewall. |

Periksa log Laravel pada `storage/logs/laravel.log`. Jangan menyalin private key,
FCM token, atau Sanctum token ke issue, log publik, maupun commit.
