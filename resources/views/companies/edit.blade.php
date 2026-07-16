@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-base-100 rounded-xl shadow-md p-6 border border-base-200/50">
            <h1 class="text-2xl font-bold mb-6">Edit Company</h1>

            <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-4"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Company Name <span
                                class="text-error">*</span></span></label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}"
                        class="input input-bordered @error('name') input-error @enderror" required>
                    @error('name')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Subdomain <span
                                class="text-error">*</span></span></label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="subdomain" value="{{ old('subdomain', $company->subdomain) }}"
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
                        class="textarea textarea-bordered @error('description') textarea-error @enderror">{{ old('description', $company->description) }}</textarea>
                    @error('description')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Status</span></label>
                    <div class="flex items-center gap-3">
                        <label class="swap">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                            <span class="swap-on text-success"><i class="fas fa-check-circle"></i> Active</span>
                            <span class="swap-off text-error"><i class="fas fa-times-circle"></i> Inactive</span>
                        </label>
                    </div>
                    @error('is_active')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-base-200">
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="fas fa-save"></i> Update Company
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
