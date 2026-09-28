@extends('layouts.app')
@section('content')
<h1>Tasks</h1>
<a href="{{ route('tasks.create') }}"><i data-lucide="plus"></i> New task</a>
<div class="skeleton-loader" aria-hidden="true"></div>
<ul>
@forelse($tasks as $task)
<li><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a> ({{ $task->status }})</li>
@empty
<li>No tasks in this company yet.</li>
@endforelse
</ul>
{{ $tasks->links() }}
@endsection
