<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PadelPro') — PadelPro</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('head')
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand">Padel<span>Pro</span></a>
        <ul class="navbar-nav">
            <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
            @auth
                @if(auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">&#128202; Admin Dashboard</a></li>
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
            @else
                <li><a href="{{ route('signin') }}" class="nav-link {{ request()->routeIs('signin') ? 'active' : '' }}">Sign In</a></li>
                <li><a href="{{ route('signup') }}" class="btn btn-primary btn-sm">Sign Up</a></li>
            @endauth
        </ul>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
    </div>
</nav>

@yield('content')

<footer>
    <div class="container">
        <p>&copy; {{ date('Y') }} PadelPro. All rights reserved.</p>
    </div>
</footer>

<script src="{{ asset('assets/js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
