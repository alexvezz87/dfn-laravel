<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaiMember extends Model
{
    use HasFactory;

    protected $table = 'fai_members';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'joined_date' => 'date',
            'verified' => 'boolean',
            'verified_at' => 'datetime',
            'is_volunteer' => 'boolean',
            'is_guide' => 'boolean',
            'has_safety_course' => 'boolean',
            'is_sivol_registered' => 'boolean',
        ];
    }

    /**
     * Get the associated user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full name attribute.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}")
        );
    }
}
