<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneratedLicenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'version_id' => Version::factory(),
            'data' => $this->faker->sha256,
        ];
    }
}
