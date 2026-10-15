<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total_persons' => 'integer',
            'persons_standard' => 'integer',
            'persons_fai' => 'integer',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'checkin_at' => 'datetime',
        ];
    }

    /**
     * Get the event associated with the booking.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
