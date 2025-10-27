<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Course;
use App\Models\Pricing;
use App\Models\CourseBatch;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $subTotal = $this->faker->numberBetween(100000, 500000);
        $tax = (int)($subTotal * 0.1);
        $grandTotal = $subTotal + $tax;

        return [
            'booking_trx_id' => strtoupper(Str::random(10)),
            'user_id' => User::inRandomOrder()->value('id') ?? 1,
            'course_id' => Course::inRandomOrder()->value('id') ?? 1,
            'pricing_id' => Pricing::inRandomOrder()->value('id') ?? 1,
            'course_batch_id' => CourseBatch::inRandomOrder()->value('id') ?? 1,
            'sub_total_amount' => $subTotal,
            'grand_total_amount' => $grandTotal,
            'total_tax_amount' => $tax,
            'is_paid' => $this->faker->boolean(80),
            'payment_type' => $this->faker->randomElement(['manual', 'midtrans', 'transfer', 'cash']),
            'proof' => $this->faker->optional()->imageUrl(640, 480, 'payment-proof', true, 'Proof'),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}
