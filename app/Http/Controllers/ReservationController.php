<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use App\Models\ItemCategory;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function create()
    {
        $pitches = $this->getTypesByCategory('Pitch');
        $balls   = $this->getTypesByCategory('Ball');
        $rackets = $this->getTypesByCategory('Racket');

        return view('reservation', compact('pitches', 'balls', 'rackets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pitch_type_id'    => ['required', 'exists:item_types,id'],
            'start_time'       => ['required', 'date', 'after:now'],
            'end_time'         => ['required', 'date', 'after:start_time'],
            'ball_type_id'     => ['nullable', 'exists:item_types,id'],
            'number_of_balls'  => ['nullable', 'integer', 'min:0', 'max:50'],
            'racket_type_id'   => ['nullable', 'exists:item_types,id'],
            'number_of_rackets'=> ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $pitchTypeId  = (int) $data['pitch_type_id'];
        $ballTypeId   = (int) ($data['ball_type_id'] ?? 0);
        $racketTypeId = (int) ($data['racket_type_id'] ?? 0);
        $numBalls     = (int) ($data['number_of_balls'] ?? 0);
        $numRackets   = (int) ($data['number_of_rackets'] ?? 0);
        $startTime    = $data['start_time'];
        $endTime      = $data['end_time'];

        // Overlap check
        $overlap = Reservation::where('pitch_type_id', $pitchTypeId)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($overlap) {
            return back()
                ->withInput()
                ->withErrors(['start_time' => 'This pitch is already booked for the selected time slot. Please choose another time.']);
        }

        $pricing = $this->calculatePrice($pitchTypeId, $ballTypeId, $racketTypeId, $numBalls, $numRackets, $startTime, $endTime);

        Reservation::create([
            'user_id'           => auth()->id(),
            'pitch_type_id'     => $pitchTypeId,
            'ball_type_id'      => ($ballTypeId > 0 && $numBalls > 0) ? $ballTypeId : null,
            'racket_type_id'    => ($racketTypeId > 0 && $numRackets > 0) ? $racketTypeId : null,
            'number_of_balls'   => $numBalls,
            'number_of_rackets' => $numRackets,
            'start_time'        => $startTime,
            'end_time'          => $endTime,
            'total_price'       => $pricing['grand_total'],
            'status'            => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Reservation submitted! Awaiting admin approval.');
    }

    private function getTypesByCategory(string $categoryName): \Illuminate\Database\Eloquent\Collection
    {
        return ItemType::whereHas('category', fn ($q) => $q->where('name', $categoryName))
            ->orderBy('price')
            ->get();
    }

    private function calculatePrice(int $pitchTypeId, int $ballTypeId, int $racketTypeId, int $numBalls, int $numRackets, string $startTime, string $endTime): array
    {
        $start = new \DateTime($startTime);
        $end   = new \DateTime($endTime);
        $hours = ($end->getTimestamp() - $start->getTimestamp()) / 3600;

        $pitchPrice  = ItemType::find($pitchTypeId)?->price ?? 0;
        $ballPrice   = ($ballTypeId > 0 && $numBalls > 0) ? (ItemType::find($ballTypeId)?->price ?? 0) : 0;
        $racketPrice = ($racketTypeId > 0 && $numRackets > 0) ? (ItemType::find($racketTypeId)?->price ?? 0) : 0;

        $pitchTotal  = $pitchPrice * $hours;
        $ballTotal   = $ballPrice * $numBalls;
        $racketTotal = $racketPrice * $numRackets;

        return [
            'hours'        => round($hours, 2),
            'pitch_total'  => $pitchTotal,
            'ball_total'   => $ballTotal,
            'racket_total' => $racketTotal,
            'grand_total'  => $pitchTotal + $ballTotal + $racketTotal,
        ];
    }
}
