@extends('layouts.app')
@section('content')
<h1>Create department</h1>
<form method="POST" action="{{ route('departments.store') }}">@csrf
<label>Name<input name="name" required></label>
<label>Description<textarea name="description"></textarea></label>
<label>Manager<select name="manager_id"><option value="">None</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></label>
<button>Create</button>
</form>
@endsection
