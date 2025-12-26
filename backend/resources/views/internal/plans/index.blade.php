@extends('internal.layouts.app')

@section('header', 'Plans')

@section('actions')
    <a href="{{ route('internal.plans.create') }}" class="btn-primary flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Create Plan
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div
                class="bg-surface shadow-sm border-2 {{ $plan->status === 'active' ? 'border-primary' : 'border-gray-200' }} rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-text">{{ $plan->name }}</h3>
                        <p class="text-sm text-text-muted">{{ $plan->slug }}</p>
                    </div>
                    @if($plan->status === 'active')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                </div>

                <div class="mb-4">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-text">₹{{ number_format($plan->price, 2) }}</span>
                        <span class="text-text-muted ml-2">/{{ $plan->interval }}</span>
                    </div>
                </div>

                @if($plan->description)
                    <p class="text-sm text-text-muted mb-4">{{ $plan->description }}</p>
                @endif

                <div class="pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center text-sm mb-4">
                        <span class="text-text-muted">Active Subscriptions</span>
                        <span class="font-semibold text-text">{{ $plan->subscriptions_count ?? 0 }}</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('internal.plans.edit', $plan->id) }}"
                            class="flex-1 text-center px-3 py-2 bg-gray-50 text-text rounded-md hover:bg-gray-100 text-sm font-medium border border-gray-200">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('internal.plans.destroy', $plan->id) }}" class="flex-1"
                            onsubmit="return confirm('Are you sure you want to delete this plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full px-3 py-2 bg-red-50 text-red-700 rounded-md hover:bg-red-100 text-sm font-medium border border-red-200">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-text-muted">No plans created yet.</p>
                <a href="{{ route('internal.plans.create') }}" class="mt-4 inline-block btn-primary">Create Your First Plan</a>
            </div>
        @endforelse
    </div>
@endsection