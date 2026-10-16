<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'total_persons' => 2,
            'persons_standard' => 1,
            'persons_fai' => 1,
            'status' => 'confirmed',
            'checkin_status' => 'pending',
            'checkin_at' => null,
            'payment_method' => 'stripe',
            'amount_due' => 25.00,
            'amount_paid' => 25.00,
        ];
    }
}
