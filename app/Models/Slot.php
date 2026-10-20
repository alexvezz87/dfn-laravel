<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    use HasFactory;

    protected $table = 'slots';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'is_locked' => 'boolean',
            'max_capacity' => 'integer',
            'overbooking_max' => 'integer',
            'booked_count' => 'integer',
        ];
    }

    /**
     * Get the event that owns the slot.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the bookings registered for this slot.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Posti residui attualmente disponibili nello slot.
     */
    public function availableCapacity(): int
    {
        $totalMax = (int) $this->max_capacity + (int) $this->overbooking_max;

        return max(0, $totalMax - (int) $this->booked_count);
    }

    /**
     * Verifica se lo slot ha raggiunto la capienza massima.
     */
    public function isFull(): bool
    {
        return $this->availableCapacity() <= 0;
    }
}
