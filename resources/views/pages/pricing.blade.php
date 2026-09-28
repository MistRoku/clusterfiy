@extends('layouts.app')
@section('content')
<h1>Pricing</h1>
<p>Two plans. Upgrade for unlimited members and Excel exports.</p>
<div>
<h2>Free plan</h2>
<p>Price: 0 USD per month. Includes 1 company per user, up to 5 members per company, tasks and dashboards. Excel exports are not included.</p>
<a href="{{ route('register') }}">Start with Free</a>
</div>
<div>
<h2>Team plan</h2>
<p>Price: 12 USD per user per month. Includes unlimited companies, unlimited members, Excel exports, priority support.</p>
<a href="{{ route('register') }}">Start with Team</a>
</div>
@endsection
