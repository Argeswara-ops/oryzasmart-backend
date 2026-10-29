# Konfigurasi Notifikasi Firebase: Flutter dan Laravel

Panduan ini menghubungkan aplikasi Flutter ORYZA SMART dengan Firebase Cloud
Messaging (FCM) dan API Laravel. Aplikasi mendapatkan FCM registration token
dan mengirimkannya ke Laravel setelah login atau pemulihan sesi.

## Alur integrasi

1. Flutter meminta izin notifikasi dan mendapatkan FCM registration token.
2. Setelah login, Flutter mengirim token ke `POST /api/user/fcm-token` dengan
   bearer token Sanctum.
3. Laravel menyimpan token untuk user yang sedang login.
4. Laravel mengirim notifikasi ke token tersebut melalui Firebase Admin SDK.
5. Saat logout, Flutter memanggil `DELETE /api/user/fcm-token`.

> FCM registration token berbeda dari Firebase Installation ID. Laravel harus
> menyimpan registration token yang diterima pada field `fcm_token`.

## Konfigurasi Firebase dan Flutter

### 1. Daftarkan aplikasi Android di Firebase

1. Buat atau pilih Firebase project di Firebase Console.
2. Tambahkan Android app dengan package name yang sama dengan
   `applicationId` di `android/app/build.gradle.kts` (saat ini
   `com.example.padiapps`).
3. Unduh `google-services.json` dan letakkan di:

   ```text
   android/app/google-services.json
   ```

4. Pastikan Google Services Gradle plugin dan dependensi Firebase tersedia.
   Keduanya sudah dikonfigurasi di project ini melalui
   `android/settings.gradle.kts`, `android/app/build.gradle.kts`, dan
   `pubspec.yaml`.
5. Pastikan `lib/firebase_options.dart` berisi Firebase options untuk Android
   project yang sama. Jika konfigurasi Firebase berubah, regenerasi options
   dengan FlutterFire CLI dan jangan menimpa file secara manual dengan nilai
   dari project yang berbeda.

Firebase client config diperlukan untuk menghubungkan aplikasi ke Firebase.
Jangan pernah menaruh service-account JSON atau private key server di aplikasi
Flutter, `google-services.json`, atau repository.

### 2. Atur URL API Laravel

Atur `.env` Flutter pada root project:

```dotenv
API_BASE_URL=http://10.0.2.2:8000/api
```

`10.0.2.2` digunakan emulator Android untuk mengakses host komputer. Untuk
perangkat fisik, gunakan alamat IP LAN komputer yang menjalankan Laravel.
Untuk production, gunakan URL HTTPS API yang sudah dipublikasikan.

Jalankan atau build aplikasi:

```powershell
flutter pub get
flutter run
```

Pada Android 13 atau lebih baru, izinkan notifikasi saat aplikasi meminta
permission.

## Konfigurasi Laravel

Contoh berikut menggunakan Laravel Sanctum untuk autentikasi API dan tabel
terpisah untuk token. Tabel ini menyimpan **satu token aktif per user**:
login dari perangkat baru akan mengganti token perangkat sebelumnya.

### 1. Siapkan Firebase Admin SDK

Di project Laravel, pasang Firebase Admin SDK Laravel:

```bash
composer require kreait/laravel-firebase
```

Buat service account untuk Firebase Admin SDK di Firebase Console / Google
Cloud Console. Simpan file JSON di luar web root dan repository, lalu atur
path-nya sebagai secret environment variable pada server:

```dotenv
FIREBASE_CREDENTIALS=/secure/path/firebase-service-account.json
```

Atur permission file agar hanya user service Laravel yang dapat membacanya.
Jangan commit file service account atau mengirimkannya ke Flutter. Ikuti
instruksi versi package yang terpasang bila konfigurasi Laravel Firebase
memerlukan publish config atau binding tambahan.

### 2. Buat tabel token

Buat migration:

```bash
php artisan make:migration create_fcm_tokens_table
```

Contoh migration:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('fcm_token', 512);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
```

Jalankan migration:

```bash
php artisan migrate
```

### 3. Daftarkan endpoint API

Tambahkan route dalam middleware `auth:sanctum` di `routes/api.php`:

```php
use App\Http\Controllers\Api\FcmTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/fcm-token', [FcmTokenController::class, 'store']);
    Route::delete('/user/fcm-token', [FcmTokenController::class, 'destroy']);
});
```

Buat controller:

```bash
php artisan make:controller Api/FcmTokenController
```

Contoh implementasi:

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $request->user()->fcmToken()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['fcm_token' => $validated['fcm_token']],
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved.',
            'data' => [],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->fcmToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed.',
            'data' => [],
        ]);
    }
}
```

Tambahkan relasi pada model `User`:

```php
use Illuminate\Database\Eloquent\Relations\HasOne;

public function fcmToken(): HasOne
{
    return $this->hasOne(FcmToken::class);
}
```

Buat model `app/Models/FcmToken.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    protected $fillable = ['user_id', 'fcm_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Pastikan model `User` berada di namespace `App\Models`, atau sesuaikan import
`User` pada model dan service jika namespace backend berbeda.

> Karena aplikasi mengirim request `DELETE /user/fcm-token` tanpa body,
> endpoint tersebut menghapus token user yang terautentikasi. Desain satu
> token per user berarti logout di satu perangkat akan mengganti/menghapus
> token yang digunakan perangkat lain. Untuk dukungan multi-perangkat, simpan
> banyak token per user dan ubah Flutter agar mengirim token spesifik pada
> request DELETE.

## Mengirim notifikasi dari Laravel

Inject contract `Kreait\Firebase\Contract\Messaging` pada service/controller
yang mengirim notifikasi, lalu kirim ke token user. Contoh:

```php
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

final class UserNotificationService
{
    public function __construct(private Messaging $messaging)
    {
    }

    public function sendToUser(User $user, string $title, string $body): void
    {
        $token = $user->fcmToken?->fcm_token;
        if (! $token) {
            return;
        }

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body));

        $this->messaging->send($message);
    }
}
```

Tangani exception Firebase di job/service produksi, catat kegagalan tanpa
menulis token lengkap ke log, dan hapus token dari database jika Firebase
menyatakan token sudah tidak valid.

## Verifikasi

1. Pastikan API Laravel dapat diakses dari perangkat/emulator dan URL sudah
   sesuai dengan `.env` Flutter.
2. Login ke aplikasi dengan akun yang valid.
3. Periksa tabel `fcm_tokens` untuk memastikan token tersimpan pada user
   tersebut. Jangan mencetak token lengkap ke log atau membagikannya.
4. Kirim notifikasi uji dari Laravel ke token tersimpan saat aplikasi berada di
   foreground, background, dan setelah dibuka kembali.
5. Logout, lalu pastikan record token user dihapus.

Request yang dikirim Flutter saat menyimpan token:

```http
POST /api/user/fcm-token
Authorization: Bearer <sanctum-token>
Accept: application/json
Content-Type: application/json
```

```json
{
  "fcm_token": "<fcm-registration-token>"
}
```

Request penghapusan saat logout:

```http
DELETE /api/user/fcm-token
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Jika login berhasil tetapi token belum tersimpan, pastikan Firebase Android
config cocok dengan `applicationId`, permission notifikasi diberikan, perangkat
memiliki Google Play services, dan endpoint Laravel berada di middleware
`auth:sanctum`.
