# Integrasi Laravel

Arsitektur production yang disarankan:

```text
Flutter -> Laravel API -> Oryza Vision API
```

Flutter cukup upload gambar ke Laravel. Laravel yang meneruskan gambar ke FastAPI memakai internal API key.

## Environment FastAPI

Di server Oryza Vision API:

```env
MODEL_PATH=models/best.pt
IMAGE_SIZE=224
HIGH_CONFIDENCE_THRESHOLD=0.80
MEDIUM_CONFIDENCE_THRESHOLD=0.60
MAX_IMAGE_SIZE_MB=10
REALTIME_INTERVAL_MS=2000
REALTIME_STABLE_FRAME_COUNT=2
REALTIME_SESSION_TTL_SECONDS=300
REQUIRE_API_KEY=true
ORYZA_VISION_API_KEY=change-this-to-a-long-random-secret
```

## Environment Laravel

Di `.env` Laravel:

```env
ORYZA_VISION_URL=http://127.0.0.1:8000
ORYZA_VISION_API_KEY=change-this-to-a-long-random-secret
```

Jika FastAPI berada di server lain, gunakan URL service tersebut:

```env
ORYZA_VISION_URL=https://vision-api.example.com
```

## Config Laravel

Tambahkan ke `config/services.php`:

```php
'oryza_vision' => [
    'url' => env('ORYZA_VISION_URL', 'http://127.0.0.1:8000'),
    'api_key' => env('ORYZA_VISION_API_KEY'),
],
```

## Route Laravel

Contoh di `routes/api.php`:

```php
use App\Http\Controllers\PaddyPredictionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('/paddy/predict', [PaddyPredictionController::class, 'store']);
Route::middleware('auth:sanctum')->post('/paddy/predict/realtime', [PaddyPredictionController::class, 'realtime']);
```

Untuk testing awal tanpa login, middleware bisa dilepas sementara.

## Controller Laravel

Contoh controller:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaddyPredictionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $image = $request->file('image');

        $response = Http::timeout(60)
            ->withHeaders([
                'X-API-Key' => config('services.oryza_vision.api_key'),
            ])
            ->attach(
                'file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )
            ->post(rtrim(config('services.oryza_vision.url'), '/') . '/predict');

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Vision prediction failed',
                'error' => $response->json(),
            ], $response->status());
        }

        $prediction = $response->json('prediction');

        // Simpan ke database di sini jika diperlukan:
        // predicted_class, confidence, prediction_status, predicted_at, image_path, user_id, device_id.

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
        ]);
    }

    public function realtime(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'stream_id' => ['nullable', 'string', 'max:100'],
        ]);

        $image = $request->file('image');

        $response = Http::timeout(30)
            ->withHeaders([
                'X-API-Key' => config('services.oryza_vision.api_key'),
            ])
            ->attach(
                'file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )
            ->post(rtrim(config('services.oryza_vision.url'), '/') . '/predict/realtime', [
                'stream_id' => $request->input('stream_id'),
            ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Realtime vision prediction failed',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json());
    }

}
```

## Request dari Flutter ke Laravel

Flutter mengirim field `image` ke:

```text
POST /api/paddy/predict
```

Laravel akan mengubah field tersebut menjadi field `file` saat meneruskan request ke FastAPI.

Untuk kamera realtime, Flutter mengirim snapshot berkala ke:

```text
POST /api/paddy/predict/realtime
```

Field:

```text
image: snapshot kamera JPG/PNG
stream_id: ID tetap selama halaman kamera aktif
```

Gunakan interval sekitar `2 detik`, bukan 30 FPS. Tampilkan `stable_prediction` jika tersedia;
jika masih `null`, tampilkan `prediction` sebagai indikasi sementara dan beri status menunggu stabil.

## Endpoint FastAPI

Laravel memanggil:

```text
POST /predict
Header: X-API-Key: <secret>
Body: multipart/form-data
Field: file
```

Untuk realtime:

```text
POST /predict/realtime
Header: X-API-Key: <secret>
Body: multipart/form-data
Field: file
Field: stream_id
```

Contoh response:

```json
{
  "success": true,
  "prediction": {
    "class_id": 3,
    "class": "blast",
    "display_name": "Blast",
    "confidence": 92.74,
    "status": "high_confidence"
  }
}
