@extends('layouts.app')
@section('content')
<h1>Clusterfiy home</h1>
<p>One login. Multiple companies. Tasks, departments and reports scoped to the active company.</p>
@push('structured-data')
<script type="application/ld+json">{"@context":"https://schema.org","@type":"SoftwareApplication","name":"Clusterfiy","applicationCategory":"BusinessApplication","operatingSystem":"Web","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"}}</script>
@endpush
<div class="skeleton-loader" aria-hidden="true"></div>
<h2>How it works</h2>
<div style="display:flex;gap:16px;overflow-x:auto;">
<div class="border border-neutral-300 bg-white p-4" style="min-width:260px;"><i data-lucide="building-2"></i><h3>Create a company</h3><p>Owners create a company and invite members by email.</p></div>
<div class="border border-neutral-300 bg-white p-4" style="min-width:260px;"><i data-lucide="kanban-square"></i><h3>Manage tasks</h3><p>Managers assign work. Members update status, comment and log time.</p></div>
<div class="border border-neutral-300 bg-white p-4" style="min-width:260px;"><i data-lucide="bar-chart-3"></i><h3>Review reports</h3><p>Dashboards show workload, overdue items and completion trends.</p></div>
<div class="border border-neutral-300 bg-white p-4" style="min-width:260px;"><i data-lucide="bell"></i><h3>Stay notified</h3><p>Assignments and invites arrive in app and by email.</p></div>
</div>
<img src="{{ asset('images/og-default.png') }}" alt="Clusterfiy dashboard preview showing task list" width="800" height="400">
@endsection
