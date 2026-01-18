@extends('internal.layouts.app')

@section('header', 'Subscriptions & Payments')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden p-5">
            <dt class="text-sm font-medium text-text-muted truncate">Total Businesses</dt>
            <dd class="mt-1 text-3xl font-semibold text-text">{{ $totalBusinesses }}</dd>
        </div>
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden p-5">
            <dt class="text-sm font-medium text-text-muted truncate">Active Subscriptions</dt>
            <dd class="mt-1 text-3xl font-semibold text-text">{{ $activeSubscriptions }}</dd>
        </div>
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden p-5">
            <dt class="text-sm font-medium text-text-muted truncate">Est. Monthly Revenue</dt>
            <dd class="mt-1 text-3xl font-semibold text-text">₹{{ number_format($monthlyRevenue) }}</dd>
        </div>
    </div>

    <!-- Subscription List -->
    <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-text">Payment History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">
                            Business</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Plan
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Amount
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Type
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Date
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-text">{{ $sub->business->name ?? 'Unknown' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $sub->plan->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text">
                                ₹{{ $sub->plan->price ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                @if (isset($sub->meta['type']) && $sub->meta['type'] === 'one_time')
                                    One-time
                                @else
                                    Recurring
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($sub->status === 'active')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                {{ $sub->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-text-muted">
                                No subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($subscriptions->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
@endsection
