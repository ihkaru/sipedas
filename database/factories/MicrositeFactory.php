<?php

namespace Database\Factories;

use App\Models\Microsite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MicrositeFactory extends Factory
{
    protected $model = Microsite::class;

    public function definition()
    {
        $title = fake()->company();
        return [
            'user_id' => User::factory(),
            'slug' => Str::slug($title . '-' . Str::random(6)),
            'title' => $title,
            'description' => fake()->sentence(),
            'theme' => fake()->randomElement(['default', 'dark', 'light']),
            'is_active' => fake()->boolean(80), // 80% chance of being active
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
