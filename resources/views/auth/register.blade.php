@extends('layouts.app')
@section('content')
<h1>Create your Clusterfiy account</h1>
<p>One login for every company you belong to. Start on the Free plan: 1 company, 5 members.</p>
<form method="POST" action="{{ route('register') }}">@csrf
<label>Name<input type="text" name="name" value="{{ old('name') }}" required></label>
<label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
<label>Password<input type="password" name="password" required></label>
<label>Confirm password<input type="password" name="password_confirmation" required></label>
<button>Register</button>
</form>
<p>Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
