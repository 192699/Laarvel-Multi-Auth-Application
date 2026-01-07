@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
<h1>Admin Login</h1>

<form method="POST" action="{{ route('admin.login') }}">
    @csrf

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="remember"> Remember me
        </label>
    </div>

    <button type="submit" class="btn">Login</button>
</form>

<p>Don't have an account? <a href="{{ route('admin.register') }}">Register here</a></p>
@endsection

