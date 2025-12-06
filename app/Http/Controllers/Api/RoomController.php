<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    // Главная страница — все типы номеров
    public function types()
    {
        $rooms = Room::where('is_active', true)
            ->get()
            ->groupBy('name');

        $result = $rooms->map(function ($group) {
            $cheapest = $group->sortBy('price_per_night')->first();

            return [
                'type_name'       => $cheapest->name,
                'slug'            => Str::slug($cheapest->name),
                'cheapest_price'  => (int) $cheapest->price_per_night,
                'capacity'        => $group->max('capacity'),
                'available_count' => $group->count(),
                'photos'          => is_string($cheapest->photos)
                    ? json_decode($cheapest->photos, true) ?? []
                    : ($cheapest->photos ?? []),
                'amenities'       => is_string($cheapest->amenities)
                    ? json_decode($cheapest->amenities, true) ?? []
                    : ($cheapest->amenities ?? []),
                'description'     => $cheapest->description ?? 'Уютный номер',
                'cheapest_room_id' => $cheapest->id,   // ← ВОТ ЭТО!
            ];
        })->values();

        return response()->json($result);
    }

    // Поиск по датам
    public function available(Request $request)
    {
        try {
            $request->validate([
                'check_in'  => 'required|date',
                'check_out' => 'required|date|after:check_in',
                'guests'    => 'nullable|integer|min:1',
            ]);

            $checkIn  = $request->input('check_in');
            $checkOut = $request->input('check_out');
            $guests   = $request->input('guests', 1);

            Log::info("Searching available rooms", [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => $guests
            ]);

            $bookedRoomIds = Booking::where('status', '!=', 'cancelled')
                ->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                        ->orWhereRaw('? BETWEEN check_in_date AND check_out_date', [$checkIn])
                        ->orWhereRaw('? BETWEEN check_in_date AND check_out_date', [$checkOut]);
                })
                ->pluck('room_id');

            Log::info("Booked room IDs found", ['count' => $bookedRoomIds->count()]);

            $availableRooms = Room::where('is_active', true)
                ->where('capacity', '>=', $guests)
                ->whereNotIn('id', $bookedRoomIds)
                ->get()
                ->groupBy('name');

            $result = $availableRooms->values()->map(function ($group, $index) {
                $cheapest = $group->sortBy('price_per_night')->first();

                return [
                    'id'              => $index + 1, // Простой id на основе индекса
                    'name'            => $cheapest->name,
                    'slug'            => Str::slug($cheapest->name),
                    'price_per_night' => $cheapest->price_per_night,
                    'capacity'        => $group->max('capacity'),
                    'available_count' => $group->count(),
                    'photos'          => is_string($cheapest->photos)
                        ? json_decode($cheapest->photos, true) ?? []
                        : ($cheapest->photos ?? []),
                    'amenities'       => is_string($cheapest->amenities)
                        ? json_decode($cheapest->amenities, true) ?? []
                        : ($cheapest->amenities ?? []),
                    'description'     => $cheapest->description ?? 'Уютный номер',
                ];
            });

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("Error in available rooms search: " . $e->getMessage(), [
                'check_in' => $request->input('check_in'),
                'check_out' => $request->input('check_out'),
                'guests' => $request->input('guests'),
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // Детали типа по slug
    public function show($slug)
    {
        $rooms = Room::where('is_active', true)
            ->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($slug)])
            ->get()
            ->groupBy('name');

        if ($rooms->isEmpty()) {
            return response()->json(['message' => 'Room type not found'], 404);
        }

        $group = $rooms->first();
        $cheapest = $group->sortBy('price_per_night')->first();

        $result = [
            'id'              => 1, // Заглушка, можно использовать crc32($slug)
            'name'            => $cheapest->name,
            'slug'            => Str::slug($cheapest->name),
            'price_per_night' => $cheapest->price_per_night,
            'capacity'        => $group->max('capacity'),
            'available_count' => $group->count(),
            'photos'          => is_string($cheapest->photos) ? json_decode($cheapest->photos, true) ?? [] : ($cheapest->photos ?? []),
            'amenities'       => is_string($cheapest->amenities) ? json_decode($cheapest->amenities, true) ?? [] : ($cheapest->amenities ?? []),
            'description'     => $cheapest->description ?? 'Уютный номер',
        ];

        return response()->json($result);
    }
}
