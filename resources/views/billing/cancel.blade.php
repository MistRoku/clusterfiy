@extends('layouts.app')
@section('content')
<h1>Checkout cancelled</h1>
<p>No charge was made. {{ $company->name }} stays on the {{ $company->plan }} plan.</p>
<a href="{{ route('billing.show') }}">Back to billing</a>
@endsection
