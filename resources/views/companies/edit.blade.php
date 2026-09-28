@extends('layouts.app')
@section('content')
<h1>Edit company: {{ $company->name }}</h1>
<form method="POST" action="{{ route('companies.update', $company) }}">@csrf @method('PUT')
<label>Name<input name="name" value="{{ $company->name }}"></label>
<button>Save</button>
</form>
@endsection
