<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    // GET /api/hotels  (?city=Ohrid)
    public function index(Request $request)
    {
        $hotels = Hotel::query()
            ->withCount('rooms')
            ->when($request->query('city'), function ($query, $city) {
                $query->whereRaw('LOWER(city) = ?', [strtolower($city)]);
            })
            ->orderBy('name')
            ->get();

        return HotelResource::collection($hotels);
    }

    // GET /api/hotels/{hotel}
    public function show(Hotel $hotel)
    {
        $hotel->load('rooms');

        return new HotelResource($hotel);
    }
}