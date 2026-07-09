@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                @include('partials.update-profile-information-form')
                @include('partials.update-password-form')
                @include('partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
