@extends('layouts.app')
@section('content')
<h1>Log in to Clusterfiy</h1>
<form method="POST" action="{{ route('login') }}">@csrf
<label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
<label>Password<input type="password" name="password" required></label>
<label><input type="checkbox" name="remember"> Remember me</label>
<button>Log in</button>
</form>
<p><a href="{{ route('password.request') }}">Forgot your password?</a></p>
<p>No account? <a href="{{ route('register') }}">Register</a></p>
@endsection
