@extends('layouts.app')
@section('content')
<h1>Choose a new password</h1>
<form method="POST" action="{{ route('password.update') }}">@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<label>Email<input type="email" name="email" value="{{ old('email', $request->email) }}" required></label>
<label>New password<input type="password" name="password" required></label>
<label>Confirm password<input type="password" name="password_confirmation" required></label>
<button>Reset password</button>
</form>
@endsection
