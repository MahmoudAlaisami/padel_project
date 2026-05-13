<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $now   = now();

        $totalRes  = $user->reservations()->count();
        $upcoming  = $user->reservations()
                          ->where('start_time', '>', $now)
                          ->where('status', 'approved')
                          ->count();
        $totalSpent = $user->reservations()
                           ->where('status', '!=', 'cancelled')
                           ->sum('total_price');

        $reservations = $user->reservations()
            ->with(['pitchType', 'ballType', 'racketType'])
            ->latest()
            ->get();

        return view('dashboard', compact('totalRes', 'upcoming', 'totalSpent', 'reservations'));
    }

    public function cancel(Request $request)
    {
        $request->validate(['cancel_id' => ['required', 'integer']]);

        $reservation = Reservation::where('id', $request->cancel_id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($reservation) {
            $reservation->update(['status' => 'cancelled']);
            return redirect()->route('dashboard')->with('success', 'Reservation cancelled.');
        }

        return redirect()->route('dashboard')->with('error', 'Reservation not found or cannot be cancelled.');
    }
}
