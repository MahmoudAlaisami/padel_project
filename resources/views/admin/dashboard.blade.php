@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('sidebar')
    <div class="sidebar-section">Admin Panel</div>
    <a href="{{ route('admin.dashboard') }}" class="active">&#128202; Dashboard</a>
    <a href="{{ route('admin.reservations') }}">&#128203; Reservations</a>
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
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-sub">Overview of the padel reservation system.</p>

    <!-- Stats -->
    <div class="grid-4 mb-4">
        <div class="card stat-card">
            <div class="icon">&#128101;</div>
            <div class="card-value">{{ $totalUsers }}</div>
            <div class="card-label">Registered Users</div>
        </div>
        <div class="card stat-card">
            <div class="icon">&#128203;</div>
            <div class="card-value">{{ $totalRes }}</div>
            <div class="card-label">Total Reservations</div>
        </div>
        <div class="card stat-card">
            <div class="icon">&#128176;</div>
            <div class="card-value">${{ number_format($totalRev, 2) }}</div>
            <div class="card-label">Total Revenue</div>
        </div>
        <div class="card stat-card">
            <div class="icon">&#9201;</div>
            <div class="card-value">{{ $pendingRes }}</div>
            <div class="card-label">Pending Approvals</div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="flex gap-2 mb-4" style="flex-wrap:wrap;">
        <a href="{{ route('admin.reservations') }}" class="btn btn-primary">&#128203; Manage Reservations</a>
        <a href="{{ route('admin.items') }}" class="btn btn-outline">&#127907; Manage Items</a>
        <a href="{{ route('admin.users') }}" class="btn btn-outline">&#128101; Manage Users</a>
    </div>

    <!-- Recent reservations -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--dark-3); display:flex; justify-content:space-between; align-items:center;">
            <h2 class="card-title" style="margin:0;">Recent Reservations</h2>
            <a href="{{ route('admin.reservations') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Pitch</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recent as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->user?->full_name }}</td>
                        <td>{{ $r->pitchType?->type_name }}</td>
                        <td>
                            {{ $r->start_time->format('M d, Y') }}
                            <small style="color:var(--text-muted);display:block;">
                                {{ $r->start_time->format('H:i') }} – {{ $r->end_time->format('H:i') }}
                            </small>
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
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">No reservations yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
