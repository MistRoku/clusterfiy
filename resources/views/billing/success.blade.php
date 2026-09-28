@extends('layouts.app')
@section('content')
<h1>Subscription confirmed</h1>
<p>Thanks. The Team plan for {{ $company->name }} is being activated. The webhook confirms payment within a minute; exports unlock automatically.</p>
<a href="{{ route('billing.show') }}">Back to billing</a>
@endsection
