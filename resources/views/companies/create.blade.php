@extends('layouts.app')
@section('content')
<h1>Create company</h1>
<form method="POST" action="{{ route('companies.store') }}">@csrf
<label>Name<input name="name" required></label>
<label>Subdomain<input name="subdomain" required></label>
<button>Create</button>
</form>
@endsection
