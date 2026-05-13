<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalRes   = Reservation::count();
        $totalRev   = Reservation::where('status', 'approved')->sum('total_price');
        $pendingRes = Reservation::where('status', 'pending')->count();

        $recent = Reservation::with(['user', 'pitchType'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('totalUsers', 'totalRes', 'totalRev', 'pendingRes', 'recent'));
    }
}
