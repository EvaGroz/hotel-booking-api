<?php

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function createHotelWithRoom(): Hotel
{
    $hotel = Hotel::create([
        'name' => 'Test Hotel',
        'city' => 'Skopje',
        'country' => 'North Macedonia',
        'address' => 'Test Street 1',
        'stars' => 4,
    ]);

    $hotel->rooms()->create([
        'room_number' => '101',
        'type' => 'double',
        'capacity' => 2,
        'price_per_night' => 100,
    ]);

    return $hotel;
}

function fakeUsdRate(float $rate = 1.1): void
{
    Http::fake([
        'api.frankfurter.dev/*' => Http::response([
            ['date' => '2026-11-23', 'base' => 'EUR', 'quote' => 'USD', 'rate' => $rate],
        ]),
    ]);
}

it('shows room prices converted to another currency', function () {
    fakeUsdRate(1.1);
    $hotel = createHotelWithRoom();

    $this->getJson("/api/hotels/{$hotel->id}?currency=USD")
        ->assertOk()
        ->assertJsonPath('data.rooms.0.converted_price.currency', 'USD')
        ->assertJsonPath('data.rooms.0.converted_price.amount', fn ($amount) => $amount == 110);
});

it('caches the exchange rate instead of calling the API every time', function () {
    fakeUsdRate();
    $hotel = createHotelWithRoom();

    $this->getJson("/api/hotels/{$hotel->id}?currency=USD")->assertOk();
    $this->getJson("/api/hotels/{$hotel->id}?currency=USD")->assertOk();

    Http::assertSentCount(1);
});

it('does not call the external API when no currency is requested', function () {
    Http::fake();
    $hotel = createHotelWithRoom();

    $this->getJson("/api/hotels/{$hotel->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.rooms.0.converted_price');

    Http::assertNothingSent();
});

it('rejects an unsupported currency', function () {
    Http::fake();
    $hotel = createHotelWithRoom();

    $this->getJson("/api/hotels/{$hotel->id}?currency=XYZ")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currency');

    Http::assertNothingSent();
});

it('returns 503 when the exchange rate service is down', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(null, 500),
    ]);
    $hotel = createHotelWithRoom();

    $this->getJson("/api/hotels/{$hotel->id}?currency=USD")
        ->assertStatus(503);
});