<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room_number',
        'type',
        'capacity',
        'price_per_night',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
        ];
    }

    // Собата припаѓа на еден хотел
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    // Една соба има многу резервации
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}