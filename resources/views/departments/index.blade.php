@extends('layouts.app')
@section('content')
<h1>Departments</h1>
<a href="{{ route('departments.create') }}">New department</a>
<ul>@forelse($departments as $d)<li>{{ $d->name }}</li>@empty<li>No departments.</li>@endforelse</ul>
{{ $departments->links() }}
@endsection
