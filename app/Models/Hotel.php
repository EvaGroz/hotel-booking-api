<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'country',
        'address',
        'stars',
        'description',
    ];

    // Еден хотел има многу соби
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}