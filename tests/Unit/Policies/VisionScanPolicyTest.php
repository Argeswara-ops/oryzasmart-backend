<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\VisionScan;
use App\Policies\VisionScanPolicy;
use Tests\TestCase;

class VisionScanPolicyTest extends TestCase
{
    public function test_allows_authenticated_users_to_list_and_create_scans(): void
    {
        $user = new User;
        $policy = new VisionScanPolicy;

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->create($user));
    }

    public function test_allows_owner_to_view_scan(): void
    {
        $user = new User;
        $user->id = 10;
        $visionScan = new VisionScan;
        $visionScan->user_id = 10;

        $response = (new VisionScanPolicy)->view($user, $visionScan);

        $this->assertTrue($response->allowed());
    }

    public function test_hides_scan_from_non_owner(): void
    {
        $user = new User;
        $user->id = 10;
        $visionScan = new VisionScan;
        $visionScan->user_id = 20;

        $response = (new VisionScanPolicy)->view($user, $visionScan);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }
}
