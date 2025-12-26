@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.subscriptions.index') }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Manual Subscription
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ route('internal.subscriptions.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <!-- Business Selection -->
                    <div>
                        <label for="business_id" class="block text-sm font-medium text-text-muted mb-1">Business</label>
                        <select name="business_id" id="business_id" class="input-field" required>
                            <option value="">Select a business...</option>
                            @foreach($businesses as $b)
                                <option value="{{ $b->id }}" {{ (request('business_id') == $b->id || old('business_id') == $b->id) ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->slug }})
                                </option>
                            @endforeach
                        </select>
                        @error('business_id') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                        @if($businesses->isEmpty())
                            <p class="text-sm text-warning mt-1">No businesses found without active subscriptions.</p>
                        @endif
                    </div>

                    <!-- Plan Selection -->
                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-text-muted mb-1">Plan</label>
                        <select name="plan_id" id="plan_id" class="input-field" required>
                            <option value="">Select a plan...</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} - ₹{{ number_format($plan->price, 2) }}/{{ $plan->interval }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-text-muted mb-1">Initial Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="active">Active (Bill immediately)</option>
                            <option value="trialing">Trialing</option>
                        </select>
                    </div>

                    <!-- Trial Days -->
                    <div id="trial-days-wrapper" class="hidden">
                        <label for="trial_days" class="block text-sm font-medium text-text-muted mb-1">Trial Days</label>
                        <input type="number" name="trial_days" id="trial_days" class="input-field" value="14" min="1">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary">Create Subscription</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const statusSelect = document.getElementById('status');
        const trialWrapper = document.getElementById('trial-days-wrapper');

        function toggleTrial() {
            if (statusSelect.value === 'trialing') {
                trialWrapper.classList.remove('hidden');
            } else {
                trialWrapper.classList.add('hidden');
            }
        }

        statusSelect.addEventListener('change', toggleTrial);
        // Init
        toggleTrial();
    </script>
@endsection