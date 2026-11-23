<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'country' => $this->country,
            'address' => $this->address,
            'stars' => $this->stars,
            'description' => $this->description,
            'rooms_count' => $this->whenCounted('rooms'),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
        ];
    }
}