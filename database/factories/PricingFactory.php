<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PricingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Basic Plan',
                'Standard Plan',
                'Premium Plan',
                'Exclusive Plan'
            ]),
            'price' => $this->faker->numberBetween(50000, 300000),
            'duration' => $this->faker->randomElement([30, 60, 90, 180]), // jumlah hari akses
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
