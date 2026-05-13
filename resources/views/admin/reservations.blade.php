@extends('layouts.dashboard')

@section('title', 'Manage Reservations')

@section('sidebar')
    <div class="sidebar-section">Admin Panel</div>
    <a href="{{ route('admin.dashboard') }}">&#128202; Dashboard</a>
    <a href="{{ route('admin.reservations') }}" class="active">&#128203; Reservations</a>
    <a href="{{ route('admin.items') }}">&#127907; Item Management</a>
    <a href="{{ route('admin.users') }}">&#128101; Users</a>
    <div class="sidebar-section">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border-radius:.6rem;color:var(--text-muted);font-weight:500;transition:all .2s ease;background:none;border:none;cursor:pointer;width:100%;font-size:1rem;">
            &#128682; Sign Out
        </button>
    </form>
@endsection

@section('main')
    <h1 class="page-title">Reservations</h1>
    <p class="page-sub">View, approve, cancel, or delete reservations.</p>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.reservations') }}" class="card mb-3" style="padding:1.25rem;">
        <div class="flex gap-2" style="flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:160px;">
                <label>Filter by User</label>
                <input type="text" name="user" class="form-control" placeholder="Name..."
                    value="{{ request('user') }}">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                <label>Filter by Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.reservations') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Pitch</th>
                        <th>Date &amp; Time</th>
                        <th>Gear</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reservations as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>
                            <div>{{ $r->user?->full_name }}</div>
                            <small style="color:var(--text-muted);">{{ $r->user?->email }}</small>
                        </td>
                        <td>{{ $r->pitchType?->type_name }}</td>
                        <td>
                            {{ $r->start_time->format('M d, Y') }}
                            <small style="color:var(--text-muted);display:block;">
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
                            <div class="flex gap-1" style="flex-wrap:wrap;">
                                @if($r->status === 'pending')
                                <form method="POST" action="{{ route('admin.reservations.action') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="res_id" value="{{ $r->id }}">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-info btn-sm">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reservations.action') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="res_id" value="{{ $r->id }}">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn-warning btn-sm"
                                        data-confirm="Cancel this reservation?">Cancel</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.reservations.action') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="res_id" value="{{ $r->id }}">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Permanently delete this reservation?">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2.5rem;">No reservations found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
