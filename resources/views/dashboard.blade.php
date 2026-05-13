@extends('layouts.dashboard')

@section('title', 'My Dashboard')

@section('sidebar')
    <div class="sidebar-section">Menu</div>
    <a href="{{ route('dashboard') }}" class="active">&#128203; My Reservations</a>
    <a href="{{ route('reservation.create') }}">&#10133; New Booking</a>
    <div class="sidebar-section">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border-radius:.6rem;color:var(--text-muted);font-weight:500;transition:all .2s ease;background:none;border:none;cursor:pointer;width:100%;font-size:1rem;">
            &#128682; Sign Out
        </button>
    </form>
@endsection

@section('main')
    <h1 class="page-title">Welcome, {{ auth()->user()->full_name }}!</h1>
    <p class="page-sub">Here's an overview of your padel bookings.</p>

    <!-- Stats -->
    <div class="grid-3 mb-4">
        <div class="card stat-card">
            <div class="icon">&#128203;</div>
            <div class="card-value">{{ $totalRes }}</div>
            <div class="card-label">Total Reservations</div>
        </div>
        <div class="card stat-card">
            <div class="icon">&#9201;</div>
            <div class="card-value">{{ $upcoming }}</div>
            <div class="card-label">Upcoming Sessions</div>
        </div>
        <div class="card stat-card">
            <div class="icon">&#128176;</div>
            <div class="card-value">${{ number_format($totalSpent, 2) }}</div>
            <div class="card-label">Total Spent</div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('reservation.create') }}" class="btn btn-primary">&#10133; Book a Pitch</a>
    </div>

    <!-- Reservations table -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--dark-3);">
            <h2 class="card-title" style="margin:0;">Reservation History</h2>
        </div>
        @if($reservations->isEmpty())
            <div style="padding:3rem;text-align:center;color:var(--text-muted);">
                <div style="font-size:3rem;margin-bottom:1rem;">&#127934;</div>
                <p>No reservations yet. <a href="{{ route('reservation.create') }}">Book your first pitch!</a></p>
            </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pitch</th>
                        <th>Date &amp; Time</th>
                        <th>Gear</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($reservations as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->pitchType?->type_name }}</td>
                        <td>
                            <div>{{ $r->start_time->format('M d, Y') }}</div>
                            <small style="color:var(--text-muted);">
                                {{ $r->start_time->format('H:i') }} – {{ $r->end_time->format('H:i') }}
                            </small>
                        </td>
                        <td style="font-size:.82rem;color:var(--text-muted);">
                            @if($r->ballType)Balls: {{ $r->ballType->type_name }} ×{{ $r->number_of_balls }}<br>@endif
                            @if($r->racketType)Rackets: {{ $r->racketType->type_name }} ×{{ $r->number_of_rackets }}@endif
                            @if(!$r->ballType && !$r->racketType)&mdash;@endif
                        </td>
                        <td style="font-weight:700;color:var(--green);">${{ number_format($r->total_price, 2) }}</td>
                        <td>
                            @php
                                $badge = match($r->status) {
                                    'approved'  => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    default     => 'badge-warning',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($r->status) }}</span>
                        </td>
                        <td>
                            @if($r->status === 'pending')
                            <form method="POST" action="{{ route('dashboard.cancel') }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="cancel_id" value="{{ $r->id }}">
                                <button type="submit" class="btn btn-danger btn-sm"
                                    data-confirm="Cancel this reservation?">Cancel</button>
                            </form>
                            @else
                                <span style="color:var(--muted);font-size:.82rem;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endsection
