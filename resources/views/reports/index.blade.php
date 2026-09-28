@extends('layouts.app')
@section('content')
<h1>Reports</h1>
<p>Total: {{ $total ?? 0 }}. Completion rate: {{ $completion_rate ?? 0 }} percent.</p>
<h2>By status</h2>
<ul>@foreach($by_status ?? [] as $s => $c)<li>{{ $s }}: {{ $c }}</li>@endforeach</ul>
<h2>By assignee</h2>
<ul>@foreach($by_assignee ?? [] as $a => $c)<li>{{ $a }}: {{ $c }}</li>@endforeach</ul>
@endsection
