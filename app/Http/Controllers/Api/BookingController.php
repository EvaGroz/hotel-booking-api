<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    // POST /api/bookings
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        $room = Room::findOrFail($data['room_id']);

        if (! $room->isAvailable($data['check_in'], $data['check_out'])) {
            return response()->json([
                'message' => 'The room is not available for the selected dates.',
            ], 409);
        }

        $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));

        $booking = $room->bookings()->create([
            ...$data,
            'total_price' => $nights * $room->price_per_night,
            'status' => 'confirmed',
        ]);

        $booking->load('room.hotel');

        return (new BookingResource($booking))->response()->setStatusCode(201);
    }

    // GET /api/bookings/{booking}
    public function show(Booking $booking)
    {
        $booking->load('room.hotel');

        return new BookingResource($booking);
    }

    // PATCH /api/bookings/{booking}/cancel
    public function cancel(Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 422);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load('room.hotel');

        return new BookingResource($booking);
    }
}