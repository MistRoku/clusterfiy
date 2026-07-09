@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Company</h1>
    <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $company->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="subdomain" :value="__('Subdomain')" />
            <x-text-input id="subdomain" name="subdomain" type="text" class="mt-1 block w-full" :value="old('subdomain', $company->subdomain)"
                required />
            <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="domain" :value="__('Domain (optional)')" />
            <x-text-input id="domain" name="domain" type="text" class="mt-1 block w-full" :value="old('domain', $company->domain)" />
            <x-input-error :messages="$errors->get('domain')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="3"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $company->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ $company->is_active ? 'checked' : '' }}
                    class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Active</span>
            </label>
            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
        </div>

        <div class="flex space-x-3">
            <x-primary-button>{{ __('Update') }}</x-primary-button>
            <a href="{{ route('companies.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">Cancel</a>
        </div>
    </form>
@endsection
