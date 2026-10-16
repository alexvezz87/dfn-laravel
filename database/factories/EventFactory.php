<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'access_type' => 'slots',
            'payment_mode' => 'online',
            'price_standard' => fake()->randomFloat(2, 5, 25),
            'price_fai' => fake()->randomFloat(2, 0, 15),
            'is_active' => true,
            'event_date_start' => fake()->date(),
            'event_date_end' => fake()->date(),
            'slot_duration' => 30,
            'slot_capacity' => 20,
            'status' => 'published',
            'booking_status' => 'open',
        ];
    }
}
