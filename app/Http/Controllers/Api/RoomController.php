<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // GET /api/rooms/{room}/availability?check_in=...&check_out=...
    public function availability(Request $request, Room $room)
    {
        $validated = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        return response()->json([
            'room_id' => $room->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'available' => $room->isAvailable($validated['check_in'], $validated['check_out']),
        ]);
    }
}