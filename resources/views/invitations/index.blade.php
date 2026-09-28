@extends('layouts.app')
@section('content')
<h1>Company invitations</h1>
<form method="POST" action="{{ route('invitations.store') }}">@csrf
<label>Email<input type="email" name="email" required></label>
<label>Role<select name="role"><option value="member">Member</option><option value="manager">Manager</option><option value="owner">Owner</option></select></label>
<button>Invite</button>
</form>
<ul>
@foreach($invitations as $i)
<li>{{ $i->email }} ({{ $i->role }}) expires {{ $i->expires_at->format('Y-m-d') }}
<form method="POST" action="{{ route('invitations.destroy', $i) }}">@csrf @method('DELETE')<button>Cancel</button></form>
</li>
@endforeach
</ul>
{{ $invitations->links() }}
@endsection
