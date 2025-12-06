<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    // Список броней пользователя
    public function myBookings(Request $request)
    {
        $user = $request->attributes->get('user');
        $bookings = $user->bookings()
            ->with('room')  // Загружаем данные номера
            ->orderBy('check_in_date', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // Создать бронирование
    public function store(Request $request)
    {
        $user = $request->attributes->get('user');
        Log::info('Booking creation started', [
            'user_id' => $user ? $user->id : 'unknown',
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        if ($validator->fails()) {
            Log::warning('Booking validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Log::info('Looking for room', ['room_id' => $request->room_id, 'type' => gettype($request->room_id)]);
        $room = Room::find($request->room_id);
        if (!$room) {
            Log::warning('Room not found', ['room_id' => $request->room_id]);
            return response()->json(['error' => 'Номер не найден'], 404);
        }
        Log::info('Room found', ['room_id' => $room->id, 'room_price' => $room->price_per_night]);

        // Проверяем доступность номера (чтобы не было пересечений)
        $overlapping = Booking::where('room_id', $room->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($request) {
                $q->whereBetween('check_in_date', [$request->check_in_date, $request->check_out_date])
                  ->orWhereBetween('check_out_date', [$request->check_in_date, $request->check_out_date])
                  ->orWhereRaw('? BETWEEN check_in_date AND check_out_date', [$request->check_in_date])
                  ->orWhereRaw('? BETWEEN check_in_date AND check_out_date', [$request->check_out_date]);
            })->exists();

        if ($overlapping) {
            Log::warning('Room overlapping detected', ['room_id' => $room->id, 'dates' => [$request->check_in_date, $request->check_out_date]]);
            return response()->json(['error' => 'Номер занят на эти даты'], 409);
        }

        $data = $request->all();
        // Разбираем guest_data
        if (isset($data['guest_data'])) {
            $guestData = $data['guest_data'];
            $data['guest_name'] = $guestData['name'] ?? null;
            $data['guest_surname'] = $guestData['surname'] ?? null;
            $data['guest_second_name'] = $guestData['second_name'] ?? null;
            $data['guest_birthday'] = $guestData['birthday'] ?? null;
            $data['guest_phone'] = $guestData['phone'] ?? null;
            $data['guest_passport_series'] = $guestData['passport_series'] ?? null;
            $data['guest_passport_number'] = $guestData['passport_number'] ?? null;
            $data['guest_passport_issued_at'] = $guestData['passport_issued_at'] ?? null;
            $data['guest_passport_issued_by'] = $guestData['passport_issued_by'] ?? null;
            unset($data['guest_data']);
        }

        $booking = new Booking($data);
        $booking->user_id = $user->id;
        $booking->status = 'pending_payment';
        $booking->load('room'); // Загружаем отношение room для calculatePrice
        $booking->calculatePrice();
        Log::info('Booking price calculated', ['total_price' => $booking->total_price]);
        $booking->save();

        Log::info('Booking created successfully', [
            'booking_id' => $booking->id,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'user_id' => $booking->user_id
        ]);

        return response()->json($booking, 201);
    }

    // Оплатить бронирование
    public function pay($id, Request $request)
    {
        $user = $request->attributes->get('user');
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $user->id) {
            return response()->json(['error' => 'Не ваша бронь'], 403);
        }

        if ($booking->status !== 'pending_payment') {
            return response()->json(['error' => 'Нельзя оплатить'], 400);
        }

        $booking->status = 'paid';
        $booking->save();

        Log::info('Booking paid', ['booking_id' => $booking->id, 'user_id' => $user->id]);

        return response()->json(['message' => 'Бронь оплачена']);
    }

    // Отменить бронирование
    public function cancel($id, Request $request)
    {
        $user = $request->attributes->get('user');
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $user->id) {
            return response()->json(['error' => 'Не ваша бронь'], 403);
        }

        if (!in_array($booking->status, ['pending_payment', 'paid'])) {
            return response()->json(['error' => 'Нельзя отменить'], 400);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['message' => 'Бронь отменена']);
    }
}
