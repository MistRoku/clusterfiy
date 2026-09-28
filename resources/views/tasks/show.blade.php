@extends('layouts.app')
@section('content')
<h1>Task: {{ $task->title }}</h1>
<p>Status: {{ $task->status }}. Priority: {{ $task->priority }}</p>
<p>{{ $task->description }}</p>
<h2>Comments</h2>
<ul>@forelse($task->comments as $c)<li>{{ $c->user?->name }}: {{ $c->body }}</li>@empty<li>No comments.</li>@endforelse</ul>
<form method="POST" action="{{ route('tasks.comments', $task) }}">@csrf<input name="body" required maxlength="1000" aria-label="Comment body"><button>Comment</button></form>
<h2>Status history</h2>
<ul>@foreach($task->statusChanges as $s)<li>{{ $s->from_status }} to {{ $s->to_status }} by {{ $s->changedBy?->name }}</li>@endforeach</ul>
@endsection
