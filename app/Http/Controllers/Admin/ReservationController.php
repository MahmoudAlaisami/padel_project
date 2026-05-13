<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'pitchType', 'ballType', 'racketType'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($user = $request->get('user')) {
            $query->whereHas('user', fn ($q) => $q->where('full_name', 'like', "%{$user}%"));
        }

        if ($date = $request->get('date')) {
            $query->whereDate('start_time', $date);
        }

        $reservations = $query->get();

        return view('admin.reservations', compact('reservations'));
    }

    public function action(Request $request)
    {
        $request->validate([
            'res_id' => ['required', 'exists:reservations,id'],
            'action' => ['required', 'in:approve,cancel,delete'],
        ]);

        $reservation = Reservation::findOrFail($request->res_id);

        match ($request->action) {
            'approve' => $reservation->update(['status' => 'approved']),
            'cancel'  => $reservation->update(['status' => 'cancelled']),
            'delete'  => $reservation->delete(),
        };

        $messages = [
            'approve' => 'Reservation approved.',
            'cancel'  => 'Reservation cancelled.',
            'delete'  => 'Reservation deleted.',
        ];

        return redirect()->route('admin.reservations')->with('success', $messages[$request->action]);
    }
}
