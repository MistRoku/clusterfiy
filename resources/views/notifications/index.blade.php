@extends('layouts.app')
@section('content')
<h1>Notifications</h1>
<form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button>Mark all as read</button></form>
<ul>
@forelse($notifications as $n)
<li>{{ $n->data['message'] ?? $n->type }} @if($n->read_at)(read)@else<form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf<button>Mark read</button></form>@endif</li>
@empty<li>No notifications.</li>@endforelse
</ul>
{{ $notifications->links() }}
@endsection
