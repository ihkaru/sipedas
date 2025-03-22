<?php

namespace Database\Seeders;

use App\Models\Microsite;
use App\Models\MicrositeLink;
use App\Models\User;
use Illuminate\Database\Seeder;

class MicrositeSeeder extends Seeder
{
    public function run()
    {
        // Create 10 users with microsites
        User::factory(10)->create()->each(function ($user) {
            // Each user has 1-3 microsites
            Microsite::factory(fake()->numberBetween(1, 3))
                ->create([
                    'user_id' => $user->id,
                ])
                ->each(function ($microsite) {
                    // Each microsite has 3-8 links
                    MicrositeLink::factory(fake()->numberBetween(3, 8))
                        ->create([
                            'microsite_id' => $microsite->id,
                        ]);
                });
        });

        // Create one demo microsite with specific content
        $demoUser = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
        ]);

        $demoMicrosite = Microsite::factory()->create([
            'user_id' => $demoUser->id,
            'slug' => 'demo-links',
            'title' => 'Demo Links Page',
            'description' => 'This is a demo microsite with various social media links',
            'theme' => 'default',
            'is_active' => true,
        ]);

        // Create demo links
        $demoLinks = [
            [
                'title' => 'Visit our Website',
                'url' => 'https://example.com',
                'icon' => 'mdi-web',
                'order' => 0,
            ],
            [
                'title' => 'Follow on Instagram',
                'url' => 'https://instagram.com/demo',
                'icon' => 'mdi-instagram',
                'order' => 1,
            ],
            [
                'title' => 'Connect on LinkedIn',
                'url' => 'https://linkedin.com/company/demo',
                'icon' => 'mdi-linkedin',
                'order' => 2,
            ],
            [
                'title' => 'Subscribe to Newsletter',
                'url' => 'https://newsletter.example.com',
                'icon' => 'mdi-email',
                'order' => 3,
            ],
        ];

        foreach ($demoLinks as $link) {
            MicrositeLink::factory()->create(array_merge(
                $link,
                ['microsite_id' => $demoMicrosite->id, 'is_active' => true]
            ));
        }
    }
}
