@extends('layouts.app')

@section('title', 'Create Company')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
            <h1 class="text-2xl font-bold mb-6">Create New Company</h1>

            <form method="POST" action="{{ route('companies.store') }}" class="space-y-4" enctype="multipart/form-data">
                @csrf

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Company Name <span
                                class="text-error">*</span></span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="input input-bordered @error('name') input-error @enderror" required>
                    @error('name')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Subdomain <span
                                class="text-error">*</span></span></label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}"
                            class="input input-bordered flex-1 @error('subdomain') input-error @enderror" placeholder="acme"
                            required>
                        <span class="text-sm opacity-60">.clusterfiy.com</span>
                    </div>
                    @error('subdomain')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Description</span></label>
                    <textarea name="description" rows="3"
                        class="textarea textarea-bordered @error('description') textarea-error @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Logo</span></label>
                    <input type="file" name="logo"
                        class="file-input file-input-bordered w-full @error('logo') file-input-error @enderror">
                    @error('logo')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-base-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="fas fa-save"></i> Create Company
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
