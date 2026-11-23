<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class HotelController extends Controller
{
    // GET /api/hotels  (опционално: ?city=Ohrid)
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

    // GET /api/hotels/{hotel}  (?currency=USD)
    public function show(Request $request, Hotel $hotel, CurrencyService $currencyService)
    {
        $validated = $request->validate([
            'currency' => ['nullable', 'string', Rule::in(CurrencyService::SUPPORTED)],
        ]);

        $hotel->load('rooms');

        if (! empty($validated['currency'])) {
            $currency = $validated['currency'];

            try {
                $hotel->rooms->each(function (Room $room) use ($currencyService, $currency) {
                    $room->converted_price = [
                        'currency' => $currency,
                        'amount' => $currencyService->convert((float) $room->price_per_night, $currency),
                    ];
                });
            } catch (RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], 503);
            }
        }

        return new HotelResource($hotel);
    }
}