@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.plans.index') }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Edit Plan: {{ $plan->name }}
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('internal.plans.update', $plan->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info Card -->
            <div class="card">
                <h3 class="text-lg font-medium text-text mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-text-muted mb-1">Plan Name</label>
                        <input type="text" name="name" id="name" class="input-field" required
                            value="{{ old('name', $plan->name) }}">
                        @error('name') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-text-muted mb-1">Slug</label>
                        <input type="text" name="slug" id="slug" class="input-field" value="{{ old('slug', $plan->slug) }}">
                        @error('slug') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-text-muted mb-1">Price (₹)</label>
                        <input type="number" name="price" id="price" class="input-field" required
                            value="{{ old('price', $plan->price) }}" min="0" step="0.01">
                        @error('price') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="interval" class="block text-sm font-medium text-text-muted mb-1">Billing
                            Interval</label>
                        <select name="interval" id="interval" class="input-field">
                            <option value="monthly" {{ old('interval', $plan->interval) === 'monthly' ? 'selected' : '' }}>
                                Monthly</option>
                            <option value="yearly" {{ old('interval', $plan->interval) === 'yearly' ? 'selected' : '' }}>
                                Yearly</option>
                        </select>
                        @error('interval') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-text-muted mb-1">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="input-field">{{ old('description', $plan->description) }}</textarea>
                        @error('description') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-text-muted mb-1">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="active" {{ old('status', $plan->status) === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ old('status', $plan->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive</option>
                        </select>
                        @error('status') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Features Card -->
            <div class="card">
                <h3 class="text-lg font-medium text-text mb-4">Features & Limits</h3>

                <div class="space-y-3">
                    @foreach($features as $feature)
                        @php
                            $planFeature = $plan->features->find($feature->id);
                            $isEnabled = $planFeature !== null;
                            $limit = $planFeature ? $planFeature->pivot->limit : -1;
                        @endphp
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md border border-gray-200">
                            <div class="flex-1">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="feature_enabled[{{ $feature->id }}]" value="1" {{ $isEnabled ? 'checked' : '' }} class="rounded border-gray-300 text-primary mr-3">
                                    <div>
                                        <div class="font-medium text-text">{{ $feature->name }}</div>
                                        <div class="text-sm text-text-muted">{{ $feature->description }}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="ml-4 w-32">
                                <input type="number" name="features[{{ $loop->index }}][limit]" class="input-field text-sm"
                                    placeholder="Limit" min="-1" value="{{ $limit }}">
                                <input type="hidden" name="features[{{ $loop->index }}][id]" value="{{ $feature->id }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('internal.plans.index') }}"
                    class="px-4 py-2 bg-gray-50 text-text rounded-md hover:bg-gray-100 font-medium border border-gray-200">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Update Plan
                </button>
            </div>
        </form>
    </div>
@endsection