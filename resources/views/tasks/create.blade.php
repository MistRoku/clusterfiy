@extends('layouts.app')
@section('content')
<h1>Create task</h1>
<form method="POST" action="{{ route('tasks.store') }}">@csrf
<label>Title<input name="title" required maxlength="255"></label>
<label>Description<textarea name="description"></textarea></label>
<label>Priority<select name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></label>
<label>Assignee<select name="assigned_to"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></label>
<label>Department<select name="department_id"><option value="">None</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></label>
<label>Due date<input type="date" name="due_date"></label>
<button>Create</button>
</form>
@endsection
