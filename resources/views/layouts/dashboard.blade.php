<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — PadelPro</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('head')
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand">Padel<span>Pro</span></a>
        <ul class="navbar-nav">
            @if(auth()->user()->isAdmin())
                <li><a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">&#128202; Dashboard</a></li>
                <li><a href="{{ route('admin.reservations') }}" class="nav-link {{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">Reservations</a></li>
                <li><a href="{{ route('admin.items') }}" class="nav-link {{ request()->routeIs('admin.items*') ? 'active' : '' }}">Items</a></li>
                <li><a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">Users</a></li>
            @else
                <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">&#128203; My Reservations</a></li>
                <li><a href="{{ route('reservation.create') }}" class="nav-link {{ request()->routeIs('reservation.*') ? 'active' : '' }}">&#127934; Book Now</a></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;">Sign Out</button>
                </form>
            </li>
        </ul>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        @yield('sidebar')
    </aside>
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('main')
    </main>
</div>

<footer>
    <div class="container">
        <p>&copy; {{ date('Y') }} PadelPro. All rights reserved.</p>
    </div>
</footer>

<script src="{{ asset('assets/js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
