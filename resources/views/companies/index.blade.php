@extends('layouts.app')
@section('content')
<h1>Companies</h1>
<ul>@foreach($companies as $c)<li>{{ $c->name }} ({{ $c->plan ?? 'free' }})</li>@endforeach</ul>
{{ $companies->links() }}
@endsection
