@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="auth-card">
    <div class="auth-logo">Padel<span style="color:#e2e8f0;">Pro</span></div>
    <div class="auth-subtitle">Sign in to your account</div>

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('signin.post') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control"
                value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control"
                placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Sign In</button>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('signup') }}">Sign Up</a>
    </div>
</div>
@endsection
