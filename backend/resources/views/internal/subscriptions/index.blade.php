@extends('internal.layouts.app')

@section('header', 'Subscriptions')

@section('actions')
    <a href="{{ route('internal.subscriptions.create') }}" class="btn-primary flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        New Subscription
    </a>
@endsection

@section('content')
    <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Business</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Dates</th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-text">{{ $subscription->business->name }}</div>
                                <div class="text-xs text-text-muted">{{ $subscription->business->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-text font-medium">{{ $subscription->plan->name }}</span>
                                <div class="text-xs text-text-muted">{{ ucfirst($subscription->plan->interval) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($subscription->isActive())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                @elseif($subscription->status === 'canceled')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Canceled
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                @if($subscription->onTrial())
                                    <div class="text-xs text-blue-600">Trial ends {{ $subscription->trial_ends_at->format('M d') }}</div>
                                @endif
                                <div>Ends: {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'Never' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($subscription->isActive() && $subscription->status !== 'canceled')
                                    <form method="POST" action="{{ route('internal.subscriptions.cancel', $subscription->id) }}" onsubmit="return confirm('Cancel this subscription? Access will cease immediately (or at period end depending on logic).')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-text-muted">
                                No valid subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($subscriptions->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
@endsection
