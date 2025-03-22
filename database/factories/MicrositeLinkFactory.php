<?php

namespace Database\Factories;

use App\Models\MicrositeLink;
use App\Models\Microsite;
use Illuminate\Database\Eloquent\Factories\Factory;

class MicrositeLinkFactory extends Factory
{
    protected $model = MicrositeLink::class;

    public function definition()
    {
        return [
            'microsite_id' => Microsite::factory(),
            'title' => fake()->words(2, true),
            'url' => fake()->url(),
            'icon' => fake()->randomElement(['mdi-facebook', 'mdi-instagram', 'mdi-twitter', 'mdi-linkedin', 'mdi-web']),
            'order' => fake()->numberBetween(0, 10),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }
}
