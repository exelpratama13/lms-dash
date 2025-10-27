<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pricing;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Pricing::factory()->count(3)->create([
            'name' => 'Basic Plan',
            'duration' => 30,
            'price' => 100000,
        ]);
    }
}
