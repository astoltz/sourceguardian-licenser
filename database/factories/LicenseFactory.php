<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Variation;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'display_name' => $this->faker->bs(),
            'customer_id' => Customer::factory(),
            'variation_id' => Variation::factory(),
            'version_id' => function (array $attributes) {
                // Find the project from the variation
                $variation = Variation::find($attributes['variation_id']);
                // Create a version for that project
                return Version::factory()->create(['project_id' => $variation->project_id])->id;
            },
        ];
    }
}
