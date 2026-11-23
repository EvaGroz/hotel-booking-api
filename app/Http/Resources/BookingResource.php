<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'check_in' => $this->check_in->toDateString(),
            'check_out' => $this->check_out->toDateString(),
            'nights' => (int) $this->check_in->diffInDays($this->check_out),
            'total_price' => (float) $this->total_price,
            'hotel' => $this->whenLoaded('room', fn () => $this->room->hotel->name),
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}