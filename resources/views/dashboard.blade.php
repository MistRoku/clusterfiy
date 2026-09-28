@extends('layouts.app')
@section('content')
<h1>Dashboard: {{ $company->name ?? 'Global view' }}</h1>
<p>Tasks scoped to the active company only.</p>
<h2>Tasks by status</h2>
<ul>
<li>Todo and backlog: {{ ($byStatus['todo'] ?? 0) }}</li>
<li>In progress: {{ ($byStatus['in_progress'] ?? 0) }}</li>
<li>In review: {{ ($byStatus['in_review'] ?? 0) }}</li>
<li>Done: {{ ($byStatus['done'] ?? 0) }}</li>
</ul>
<h2>Overdue</h2>
<ul>
@forelse($overdue ?? [] as $t)<li>{{ $t->title }} (due {{ $t->due_date?->format('Y-m-d') }})</li>@empty<li>No overdue tasks.</li>@endforelse
</ul>
<h2>Workload per member</h2>
<ul>
@forelse($workload ?? [] as $w)<li>{{ $w->assignee?->name ?? $w->assigned_to }}: {{ $w->open_count }} open</li>@empty<li>No open assignments.</li>@endforelse
</ul>
<h2>Completed last 30 days</h2>
<canvas id="trend" width="400" height="120" aria-label="Completed tasks trend chart" role="img"></canvas>
<script>window.__trend = @json($trend ?? []);</script>
<h2>Recent activity</h2>
<ul>
@forelse($activity ?? [] as $a)<li>{{ $a->event }} by {{ $a->user?->name }}</li>@empty<li>No activity yet.</li>@endforelse
</ul>
<h2>My tasks</h2>
<ul>
@forelse($myTasks ?? [] as $t)<li>{{ $t->title }} ({{ $t->status }})</li>@empty<li>No assigned open tasks.</li>@endforelse
</ul>
<img src="{{ asset('images/og-default.png') }}" alt="Dashboard chart illustration" width="600" height="200">
@endsection
