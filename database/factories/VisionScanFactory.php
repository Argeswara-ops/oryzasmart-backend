<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VisionScan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisionScan>
 */
class VisionScanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => fake()->unique()->uuid(),
            'scan_type' => fake()->randomElement(['disease', 'pest']),
            'class_name' => fake()->slug(2),
            'display_name' => fake()->words(2, true),
            'confidence' => fake()->randomFloat(4, 0, 1),
            'image_path' => 'vision-scans/'.fake()->numberBetween(1, 1000).'/'.fake()->uuid().'.jpg',
            'scanned_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
