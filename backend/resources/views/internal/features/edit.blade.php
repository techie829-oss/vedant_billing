@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.features.index') }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Edit Feature: {{ $feature->name }}
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ route('internal.features.update', $feature->id) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <!-- Feature Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-text-muted mb-1">Feature Name</label>
                        <input type="text" name="name" id="name" class="input-field" required
                            value="{{ old('name', $feature->name) }}">
                        @error('name') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-text-muted mb-1">Slug (Key)</label>
                        <input type="text" name="slug" id="slug" class="input-field"
                            value="{{ old('slug', $feature->slug) }}">
                        @error('slug') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-text-muted mb-1">Type</label>
                        <select name="type" id="type" class="input-field">
                            <option value="limit" {{ old('type', $feature->type) == 'limit' ? 'selected' : '' }}>Limit (Count
                                based)</option>
                            <option value="boolean" {{ old('type', $feature->type) == 'boolean' ? 'selected' : '' }}>Boolean
                                (Yes/No)</option>
                        </select>
                        @error('type') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Default Limit -->
                    <div>
                        <label for="default_limit" class="block text-sm font-medium text-text-muted mb-1">Default
                            Limit</label>
                        <input type="number" name="default_limit" id="default_limit" class="input-field" required
                            value="{{ old('default_limit', $feature->default_limit) }}" min="-1">
                        @error('default_limit') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Global Status -->
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $feature->is_active) ? 'checked' : '' }}>
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                            </div>
                            <span class="ms-3 text-sm font-medium text-text">Global Active Status</span>
                        </label>
                        <p class="text-xs text-text-muted mt-1">Disabling this will kill the feature for ALL tenants
                            immediately.</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-text-muted mb-1">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="input-field">{{ old('description', $feature->description) }}</textarea>
                        @error('description') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary">Update Feature</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection