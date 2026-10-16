<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slot>
 */
class SlotFactory extends Factory
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
            'slot_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'max_capacity' => 20,
            'overbooking_max' => 0,
            'booked_count' => 0,
            'is_locked' => false,
            'status' => 'active',
        ];
    }
}
