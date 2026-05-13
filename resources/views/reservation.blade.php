@extends('layouts.dashboard')

@section('title', 'Book a Pitch')

@section('sidebar')
    <div class="sidebar-section">Menu</div>
    <a href="{{ route('dashboard') }}">&#128203; My Reservations</a>
    <a href="{{ route('reservation.create') }}" class="active">&#10133; New Booking</a>
    <div class="sidebar-section">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border-radius:.6rem;color:var(--text-muted);font-weight:500;transition:all .2s ease;background:none;border:none;cursor:pointer;width:100%;font-size:1rem;">
            &#128682; Sign Out
        </button>
    </form>
@endsection

@section('main')
    <h1 class="page-title">Book a Pitch</h1>
    <p class="page-sub">Fill in the details below to reserve your padel court.</p>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div style="max-width: 680px;">
        <div class="card">
            <form id="reservationForm" method="POST" action="{{ route('reservation.store') }}" novalidate>
                @csrf

                <!-- Pitch -->
                <div class="form-group">
                    <label for="pitch_type_id">Pitch Type <span style="color:var(--danger)">*</span></label>
                    <select id="pitch_type_id" name="pitch_type_id" class="form-control" required>
                        <option value="">— Select a pitch —</option>
                        @foreach($pitches as $p)
                            <option value="{{ $p->id }}"
                                data-price="{{ $p->price }}"
                                {{ old('pitch_type_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->type_name }} — ${{ number_format($p->price, 2) }}/hr
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date & Time -->
                <div class="grid-2">
                    <div class="form-group">
                        <label for="start_time">Start Time <span style="color:var(--danger)">*</span></label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control"
                            value="{{ old('start_time') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time <span style="color:var(--danger)">*</span></label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control"
                            value="{{ old('end_time') }}" required>
                    </div>
                </div>

                <!-- Balls -->
                <div class="grid-2">
                    <div class="form-group">
                        <label for="ball_type_id">Ball Type</label>
                        <select id="ball_type_id" name="ball_type_id" class="form-control">
                            <option value="0" data-price="0">— None —</option>
                            @foreach($balls as $b)
                                <option value="{{ $b->id }}"
                                    data-price="{{ $b->price }}"
                                    {{ old('ball_type_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->type_name }} — ${{ number_format($b->price, 2) }}/ball
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="number_of_balls">Number of Balls</label>
                        <input type="number" id="number_of_balls" name="number_of_balls" class="form-control"
                            min="0" max="50" value="{{ old('number_of_balls', 0) }}">
                    </div>
                </div>

                <!-- Rackets -->
                <div class="grid-2">
                    <div class="form-group">
                        <label for="racket_type_id">Racket Type</label>
                        <select id="racket_type_id" name="racket_type_id" class="form-control">
                            <option value="0" data-price="0">— None —</option>
                            @foreach($rackets as $rk)
                                <option value="{{ $rk->id }}"
                                    data-price="{{ $rk->price }}"
                                    {{ old('racket_type_id') == $rk->id ? 'selected' : '' }}>
                                    {{ $rk->type_name }} — ${{ number_format($rk->price, 2) }}/racket
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="number_of_rackets">Number of Rackets</label>
                        <input type="number" id="number_of_rackets" name="number_of_rackets" class="form-control"
                            min="0" max="20" value="{{ old('number_of_rackets', 0) }}">
                    </div>
                </div>

                <!-- Price summary (JS) -->
                <div id="priceSummary" class="price-box" style="display:none;">
                    <h4 style="margin-bottom:.75rem;font-size:.95rem;">Price Summary</h4>
                    <div class="price-row"><span>Duration</span><span id="priceHours">—</span></div>
                    <div class="price-row"><span>Pitch cost</span><span id="pricePitch">—</span></div>
                    <div class="price-row"><span>Balls cost</span><span id="priceBalls">—</span></div>
                    <div class="price-row"><span>Rackets cost</span><span id="priceRackets">—</span></div>
                    <div class="price-row total"><span>Grand Total</span><span id="priceTotal">—</span></div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3 btn-lg">Confirm Reservation</button>
            </form>
        </div>
    </div>
@endsection
