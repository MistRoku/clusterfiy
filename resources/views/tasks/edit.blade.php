@extends('layouts.app')
@section('content')
<h1>Edit task: {{ $task->title }}</h1>
<form method="POST" action="{{ route('tasks.update', $task) }}">@csrf @method('PUT')
<label>Title<input name="title" value="{{ $task->title }}" required></label>
<label>Status<select name="status"><option value="todo">Todo</option><option value="in_progress">In progress</option><option value="in_review">In review</option><option value="blocked">Blocked</option><option value="done">Done</option><option value="cancelled">Cancelled</option></select></label>
<button>Save</button>
</form>
@endsection
