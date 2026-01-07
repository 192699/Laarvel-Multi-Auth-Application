@extends('layouts.app')

@section('title', 'Customer Register')

@section('content')
<h1>Customer Registration</h1>

<form method="POST" action="{{ route('customer.register') }}">
    @csrf

    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>
    </div>

    <button type="submit" class="btn">Register</button>
</form>

<p>Already have an account? <a href="{{ route('customer.login') }}">Login here</a></p>
@endsection

