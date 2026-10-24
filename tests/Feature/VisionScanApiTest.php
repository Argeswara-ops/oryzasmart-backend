<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisionScan;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VisionScanApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('protectedEndpoints')]
    public function test_returns_401_when_request_is_unauthenticated(string $method, string $uri): void
    {
        $this->json($method, $uri)->assertUnauthorized();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function protectedEndpoints(): array
    {
        return [
            'index' => ['GET', '/api/vision/scans'],
            'store' => ['POST', '/api/vision/scans'],
            'show' => ['GET', '/api/vision/scans/1'],
        ];
    }

    public function test_valid_multipart_payload_creates_scan_and_uploads_image_with_201(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/api/vision/scans', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.local_id', 'mobile-scan-001')
            ->assertJsonPath('data.client_id', 'mobile-scan-001')
            ->assertJsonPath('data.scan_type', 'disease')
            ->assertJsonPath('data.class_name', 'rice_blast')
            ->assertJsonPath('data.display_name', 'Rice Blast')
            ->assertJsonPath('data.confidence', 0.9274)
            ->assertJsonStructure([
                'data' => [
                    'id', 'local_id', 'client_id', 'scan_type', 'class_name',
                    'display_name', 'confidence', 'scanned_at', 'image_url', 'created_at',
                ],
            ]);

        $visionScan = VisionScan::query()->sole();
        $this->assertSame($user->id, $visionScan->user_id);
        $this->assertStringStartsWith('vision-scans/'.$user->id.'/', $visionScan->image_path);
        $this->assertMatchesRegularExpression(
            '#^https?://[^/]+/storage/#',
            $response->json('data.image_url'),
        );
        Storage::disk('public')->assertExists($visionScan->image_path);
        $this->assertDatabaseHas('vision_scans', [
            'user_id' => $user->id,
            'client_id' => 'mobile-scan-001',
            'scan_type' => 'disease',
        ]);
    }

    public function test_returns_422_when_required_fields_are_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/vision/scans');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'client_id', 'scan_type', 'class_name', 'display_name',
                'confidence', 'scanned_at', 'image',
            ]);
        $this->assertDatabaseEmpty('vision_scans');
    }

    public function test_returns_422_when_payload_values_are_invalid(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'client_id' => str_repeat('a', 101),
            'scan_type' => 'healthy',
            'class_name' => str_repeat('a', 101),
            'display_name' => str_repeat('a', 151),
            'confidence' => 1.01,
            'scanned_at' => '2026-10-24 08:30:00',
            'image' => UploadedFile::fake()->create('scan.gif', 10, 'image/gif'),
        ]);

        $response = $this->actingAs($user)->post('/api/vision/scans', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'client_id', 'scan_type', 'class_name', 'display_name',
                'confidence', 'scanned_at', 'image',
            ]);
        $this->assertDatabaseEmpty('vision_scans');
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_returns_422_when_image_exceeds_ten_megabytes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'image' => UploadedFile::fake()->image('large.jpg')->size(10241),
        ]);

        $response = $this->actingAs($user)->post('/api/vision/scans', $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->assertDatabaseEmpty('vision_scans');
    }

    public function test_index_returns_only_owned_scans_newest_first_and_filters_scan_type(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $olderDisease = VisionScan::factory()->for($user)->create([
            'scan_type' => 'disease',
            'scanned_at' => '2026-10-20 01:00:00',
        ]);
        $newerDisease = VisionScan::factory()->for($user)->create([
            'scan_type' => 'disease',
            'scanned_at' => '2026-10-22 01:00:00',
        ]);
        VisionScan::factory()->for($user)->create([
            'scan_type' => 'pest',
            'scanned_at' => '2026-10-23 01:00:00',
        ]);
        VisionScan::factory()->for($otherUser)->create([
            'scan_type' => 'disease',
            'scanned_at' => '2026-10-24 01:00:00',
        ]);

        $response = $this->actingAs($user)->getJson('/api/vision/scans?scan_type=disease');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newerDisease->id)
            ->assertJsonPath('data.1.id', $olderDisease->id)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_paginates_owned_scans(): void
    {
        $user = User::factory()->create();
        VisionScan::factory()->count(3)->for($user)->create();

        $response = $this->actingAs($user)->getJson('/api/vision/scans?per_page=2&page=2');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_show_returns_owned_scan_and_hides_another_users_scan(): void
    {
        $user = User::factory()->create();
        $ownedScan = VisionScan::factory()->for($user)->create();
        $otherScan = VisionScan::factory()->for(User::factory())->create();

        $this->actingAs($user)
            ->getJson('/api/vision/scans/'.$ownedScan->id)
            ->assertOk()
            ->assertJsonPath('data.id', $ownedScan->id);

        $this->actingAs($user)
            ->getJson('/api/vision/scans/'.$otherScan->id)
            ->assertNotFound();
    }

    public function test_retry_returns_existing_scan_without_creating_another_row_or_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $firstResponse = $this->actingAs($user)->post('/api/vision/scans', $this->validPayload());
        $existingScan = VisionScan::query()->sole();
        $retryPayload = $this->validPayload([
            'display_name' => 'Must Not Replace Existing Value',
            'image' => UploadedFile::fake()->image('retry.png'),
        ]);

        $response = $this->actingAs($user)->post('/api/vision/scans', $retryPayload);

        $firstResponse->assertCreated();
        $response->assertOk()
            ->assertJsonPath('data.id', $existingScan->id)
            ->assertJsonPath('data.display_name', 'Rice Blast');
        $this->assertSame(1, VisionScan::query()->count());
        $this->assertCount(1, Storage::disk('public')->allFiles('vision-scans/'.$user->id));
        Storage::disk('public')->assertExists($existingScan->image_path);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'client_id' => 'mobile-scan-001',
            'scan_type' => 'disease',
            'class_name' => 'rice_blast',
            'display_name' => 'Rice Blast',
            'confidence' => 0.9274,
            'scanned_at' => '2026-10-24T08:30:00+07:00',
            'image' => UploadedFile::fake()->image('scan.jpg', 640, 480),
        ], $overrides);
    }
}
