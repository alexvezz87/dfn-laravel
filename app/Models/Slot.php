<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
