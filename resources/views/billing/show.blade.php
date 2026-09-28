@extends('layouts.app')
@section('content')
<h1>Billing for {{ $company->name }}</h1>
<p>Current plan: {{ $company->plan }} (status: {{ $company->subscription_status }}).</p>
<p>Members: {{ $memberCount }}@if($memberLimit !== null) of {{ $memberLimit }} included@endif.</p>

@if($overLimitBy > 0)
<div role="alert" class="border border-neutral-300 bg-white p-3">
<p>This company is {{ $overLimitBy }} member(s) over the Free plan limit. Data is kept, but adding members and companies is paused until the count is compliant or the company is re-upgraded.</p>
</div>
@endif

@if($company->subscription_status === 'past_due')
<div role="alert" class="border border-neutral-300 bg-white p-3">
<p>Last payment failed. Team features stay on until {{ $company->grace_until?->format('Y-m-d') }}. Update payment details to avoid downgrade.</p>
</div>
@endif

@if($company->teamFeaturesActive())
<p>Team plan is active. Excel exports are unlocked.</p>
<form method="POST" action="{{ route('billing.portal') }}">@csrf<button type="submit"><i data-lucide="credit-card"></i> Manage subscription</button></form>
@else
<p>Upgrade to Team for unlimited members, unlimited companies and Excel exports: 12 USD per month.</p>
<form method="POST" action="{{ route('billing.checkout') }}">@csrf<input type="hidden" name="plan" value="team"><button type="submit"><i data-lucide="arrow-up-circle"></i> Upgrade to Team with Stripe Checkout</button></form>
<p>Checkout is hosted by Stripe. Card details never touch our servers.</p>
@endif

<img src="{{ asset('images/og-default.png') }}" alt="Billing overview illustration" width="600" height="200">
@endsection
