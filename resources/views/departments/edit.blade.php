@extends('layouts.app')
@section('content')
<h1>Edit department: {{ $department->name }}</h1>
<form method="POST" action="{{ route('departments.update', $department) }}">@csrf @method('PUT')
<label>Name<input name="name" value="{{ $department->name }}" required></label>
<button>Save</button>
</form>
@endsection
