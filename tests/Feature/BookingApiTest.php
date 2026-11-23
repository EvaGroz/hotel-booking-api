<?php

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTestRoom(): Room
{
    $hotel = Hotel::create([
        'name' => 'Test Hotel',
        'city' => 'Skopje',
        'country' => 'North Macedonia',
        'address' => 'Test Street 1',
        'stars' => 4,
    ]);

    return $hotel->rooms()->create([
        'room_number' => '101',
        'type' => 'double',
        'capacity' => 2,
        'price_per_night' => 100,
    ]);
}

function bookRoom(Room $room, int $fromDay, int $toDay, string $status = 'confirmed'): Booking
{
    return $room->bookings()->create([
        'guest_name' => 'John Smith',
        'guest_email' => 'john@example.com',
        'check_in' => now()->addDays($fromDay)->toDateString(),
        'check_out' => now()->addDays($toDay)->toDateString(),
        'total_price' => 300,
        'status' => $status,
    ]);
}

it('creates a booking and calculates the total price', function () {
    $room = createTestRoom();

    $this->postJson('/api/bookings', [
        'room_id' => $room->id,
        'guest_name' => 'Jane Doe',
        'guest_email' => 'jane@example.com',
        'check_in' => now()->addDays(10)->toDateString(),
        'check_out' => now()->addDays(13)->toDateString(),
    ])
        ->assertCreated()
        ->assertJsonPath('data.nights', 3)
        ->assertJsonPath('data.total_price', fn ($price) => $price == 300)
        ->assertJsonPath('data.status', 'confirmed');

    expect(Booking::count())->toBe(1);
});

it('rejects a booking when the room is already taken', function () {
    $room = createTestRoom();
    bookRoom($room, 10, 13);

    $this->postJson('/api/bookings', [
        'room_id' => $room->id,
        'guest_name' => 'Jane Doe',
        'guest_email' => 'jane@example.com',
        'check_in' => now()->addDays(11)->toDateString(),
        'check_out' => now()->addDays(14)->toDateString(),
    ])->assertStatus(409);

    expect(Booking::count())->toBe(1);
});

it('allows booking dates of a cancelled booking', function () {
    $room = createTestRoom();
    bookRoom($room, 10, 13, 'cancelled');

    $this->postJson('/api/bookings', [
        'room_id' => $room->id,
        'guest_name' => 'Jane Doe',
        'guest_email' => 'jane@example.com',
        'check_in' => now()->addDays(10)->toDateString(),
        'check_out' => now()->addDays(13)->toDateString(),
    ])->assertCreated();
});

it('validates booking data', function () {
    $this->postJson('/api/bookings', [
        'room_id' => 999,
        'guest_email' => 'not-an-email',
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(2)->toDateString(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['room_id', 'guest_name', 'guest_email', 'check_out']);
});

it('checks room availability', function () {
    $room = createTestRoom();
    bookRoom($room, 10, 13);

    $checkIn = now()->addDays(11)->toDateString();
    $checkOut = now()->addDays(12)->toDateString();

    $this->getJson("/api/rooms/{$room->id}/availability?check_in={$checkIn}&check_out={$checkOut}")
        ->assertOk()
        ->assertJsonPath('available', false);
});

it('cancels a booking only once', function () {
    $room = createTestRoom();
    $booking = bookRoom($room, 10, 13);

    $this->patchJson("/api/bookings/{$booking->id}/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    $this->patchJson("/api/bookings/{$booking->id}/cancel")
        ->assertUnprocessable();
});