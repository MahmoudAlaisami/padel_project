@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
<div class="auth-card">
    <div class="auth-logo">Padel<span style="color:#e2e8f0;">Pro</span></div>
    <div class="auth-subtitle">Create your free account</div>

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0;padding-left:1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('signup.post') }}">
        @csrf
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control"
                value="{{ old('full_name') }}" placeholder="John Doe" required autofocus>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control"
                value="{{ old('email') }}" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
            <label for="password">Password <span style="color:var(--text-muted);font-weight:400;">(min 8 characters)</span></label>
            <input type="password" id="password" name="password" class="form-control"
                placeholder="••••••••" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Create Account</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('signin') }}">Sign In</a>
    </div>
</div>
@endsection
