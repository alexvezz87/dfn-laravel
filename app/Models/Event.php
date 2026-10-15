<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price_standard' => 'decimal:2',
            'price_fai' => 'decimal:2',
            'is_active' => 'boolean',
            'is_test_event' => 'boolean',
            'event_date_start' => 'date',
            'event_date_end' => 'date',
            'booking_opening_date' => 'datetime',
        ];
    }

    /**
     * Get the slots for the event.
     */
    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    /**
     * Get the bookings for the event.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
