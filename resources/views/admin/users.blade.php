@extends('layouts.dashboard')

@section('title', 'Manage Users')

@section('sidebar')
    <div class="sidebar-section">Admin Panel</div>
    <a href="{{ route('admin.dashboard') }}">&#128202; Dashboard</a>
    <a href="{{ route('admin.reservations') }}">&#128203; Reservations</a>
    <a href="{{ route('admin.items') }}">&#127907; Item Management</a>
    <a href="{{ route('admin.users') }}" class="active">&#128101; Users</a>
    <div class="sidebar-section">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border-radius:.6rem;color:var(--text-muted);font-weight:500;transition:all .2s ease;background:none;border:none;cursor:pointer;width:100%;font-size:1rem;">
            &#128682; Sign Out
        </button>
    </form>
@endsection

@section('main')
    <h1 class="page-title">Users</h1>
    <p class="page-sub">View and manage all registered users.</p>

    <!-- Search -->
    <form method="GET" action="{{ route('admin.users') }}" class="search-bar mb-3">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
            value="{{ $search }}">
        <button type="submit" class="btn btn-primary">Search</button>
        @if($search)
            <a href="{{ route('admin.users') }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Reservations</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->full_name }}</td>
                        <td style="color:var(--text-muted);">{{ $u->email }}</td>
                        <td>
                            <span class="badge {{ $u->role === 'admin' ? 'badge-info' : 'badge-muted' }}">
                                {{ ucfirst($u->role) }}
                            </span>
                        </td>
                        <td>{{ $u->reservations_count }}</td>
                        <td style="color:var(--text-muted);font-size:.85rem;">{{ $u->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($u->id !== auth()->id())
                            <div class="flex gap-1" style="flex-wrap:wrap;">
                                @if($u->role === 'user')
                                <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                    <input type="hidden" name="action" value="promote">
                                    <button type="submit" class="btn btn-info btn-sm"
                                        data-confirm="Promote this user to admin?">Promote</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                    <input type="hidden" name="action" value="demote">
                                    <button type="submit" class="btn btn-warning btn-sm"
                                        data-confirm="Demote this admin to regular user?">Demote</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Delete this user and all their data?">Delete</button>
                                </form>
                            </div>
                            @else
                                <span style="color:var(--muted);font-size:.82rem;">(You)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
