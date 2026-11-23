<?php

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeHotel(array $attributes = []): Hotel
{
    return Hotel::create(array_merge([
        'name' => 'Test Hotel',
        'city' => 'Skopje',
        'country' => 'North Macedonia',
        'address' => 'Test Street 1',
        'stars' => 4,
    ], $attributes));
}

it('lists all hotels', function () {
    makeHotel(['name' => 'Hotel A']);
    makeHotel(['name' => 'Hotel B']);

    $this->getJson('/api/hotels')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('filters hotels by city', function () {
    makeHotel(['name' => 'Hotel A', 'city' => 'Skopje']);
    makeHotel(['name' => 'Hotel B', 'city' => 'Ohrid']);

    $this->getJson('/api/hotels?city=ohrid')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Hotel B');
});

it('shows a hotel with its rooms', function () {
    $hotel = makeHotel();
    $hotel->rooms()->create([
        'room_number' => '101',
        'type' => 'double',
        'capacity' => 2,
        'price_per_night' => 80,
    ]);

    $this->getJson("/api/hotels/{$hotel->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Test Hotel')
        ->assertJsonCount(1, 'data.rooms');
});

it('returns 404 for a hotel that does not exist', function () {
    $this->getJson('/api/hotels/999')->assertNotFound();
});