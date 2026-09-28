@extends('layouts.app')
@section('content')
<h1>Reset your password</h1>
<p>Enter your account email. Reset links expire after 30 minutes and are limited to 5 requests per hour.</p>
@if(session('status'))<div role="status">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf
<label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
<button>Send reset link</button>
</form>
@endsection
