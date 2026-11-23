<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = [
            [
                'name' => 'Hotel Vardar',
                'city' => 'Skopje',
                'country' => 'North Macedonia',
                'address' => 'Macedonia Street 10',
                'stars' => 4,
                'description' => 'Modern hotel in the city centre, close to the Stone Bridge.',
            ],
            [
                'name' => 'Lake View Resort',
                'city' => 'Ohrid',
                'country' => 'North Macedonia',
                'address' => 'Kej Makedonija 5',
                'stars' => 5,
                'description' => 'Resort with a beautiful view of Lake Ohrid.',
            ],
            [
                'name' => 'Berlin Central Inn',
                'city' => 'Berlin',
                'country' => 'Germany',
                'address' => 'Friedrichstrasse 100',
                'stars' => 3,
                'description' => 'Budget-friendly hotel near the main station.',
            ],
        ];

        $rooms = [
            ['room_number' => '101', 'type' => 'single', 'capacity' => 1, 'price_per_night' => 45.00],
            ['room_number' => '102', 'type' => 'double', 'capacity' => 2, 'price_per_night' => 70.00],
            ['room_number' => '201', 'type' => 'suite',  'capacity' => 4, 'price_per_night' => 150.00],
        ];

        // За секој хотел креирај ги трите соби
        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            foreach ($rooms as $roomData) {
                $hotel->rooms()->create($roomData);
            }
        }

        // Една примерна резервација (3 ноќи во првата соба)
        $room = Room::first();

        $room->bookings()->create([
            'guest_name' => 'John Smith',
            'guest_email' => 'john@example.com',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(8)->toDateString(),
            'total_price' => 3 * $room->price_per_night,
        ]);
    }
}