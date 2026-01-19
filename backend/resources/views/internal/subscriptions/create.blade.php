@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.tenants.show', $business->id) }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Change Plan for {{ $business->name }}
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('internal.subscriptions.store') }}">
            @csrf
            <input type="hidden" name="business_id" value="{{ $business->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach ($plans as $plan)
                    <label
                        class="relative flex flex-col p-6 bg-white border-2 rounded-lg cursor-pointer hover:border-primary transition-colors {{ old('plan_id') == $plan->id ? 'border-primary ring-2 ring-primary ring-opacity-50' : 'border-gray-200' }}">
                        <input type="radio" name="plan_id" value="{{ $plan->id }}" class="sr-only peer" required>

                        <div class="absolute top-4 right-4 text-primary opacity-0 peer-checked:opacity-100 check-icon">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                        <div class="mb-4">
                            <span
                                class="text-3xl font-bold tracking-tight text-gray-900">₹{{ number_format($plan->price) }}</span>
                            <span class="text-sm font-semibold leading-6 text-gray-600">/ {{ $plan->interval }}</span>
                        </div>

                        <p class="text-sm text-gray-500 mb-6 flex-grow ">
                            {{ $plan->description }}
                        </p>

                        <div class="space-y-2 text-sm text-gray-700 border-t pt-4">
                            @foreach ($plan->features->take(4) as $feature)
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $feature->name }}
                                    @if ($feature->pivot->limit >= 0)
                                        <span class="text-xs text-gray-500 ml-1">({{ $feature->pivot->limit }})</span>
                                    @endif
                                </div>
                            @endforeach
                            @if ($plan->features->count() > 4)
                                <div class="text-xs text-gray-500 italic">+ {{ $plan->features->count() - 4 }} more
                                    features</div>
                            @endif
                        </div>

                        <div
                            class="mt-6 w-full py-2 px-4 bg-gray-50 text-center text-sm font-medium text-gray-900 rounded-md border border-gray-200 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-colors">
                            Select Plan
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex justify-end">
                <a href="{{ route('internal.tenants.show', $business->id) }}"
                    class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50 mr-3">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Update Subscription
                </button>
            </div>
        </form>
    </div>

    <style>
        /* Custom style for radio selection focus/checked state styling if needed */
        input[type="radio"]:checked+.check-icon {
            opacity: 1;
        }

        input[type="radio"]:checked~h3 {
            color: var(--color-primary);
        }
    </style>
@endsection
