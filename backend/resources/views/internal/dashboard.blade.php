@extends('internal.layouts.app')

@section('header', 'Overview')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Card 1: Total Tenants -->
        <div class="card">
            <h3 class="text-sm font-medium text-text-muted">Total Businesses</h3>
            <p class="text-3xl font-bold text-text mt-2">{{ $totalTenants }}</p>
            <div class="mt-4 flex items-center text-sm {{ $newTenantsLast30Days > 0 ? 'text-success' : 'text-text-muted' }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span>+{{ $newTenantsLast30Days }}</span>
                <span class="text-text-muted ml-1">last 30 days</span>
            </div>
        </div>

        <!-- Stat Card 2: Active Subscriptions -->
        <div class="card">
            <h3 class="text-sm font-medium text-text-muted">Active Subscriptions</h3>
            <p class="text-3xl font-bold text-text mt-2">{{ $activeSubscriptions }}</p>
            <div class="mt-4 flex items-center text-sm text-info">
                <span>Current verified tenants</span>
            </div>
        </div>

        <!-- Stat Card 3: Monthly Revenue -->
        <div class="card">
            <h3 class="text-sm font-medium text-text-muted">Monthly Revenue (MRR)</h3>
            <p class="text-3xl font-bold text-text mt-2">₹{{ number_format($mrr, 2) }}</p>
            <div class="mt-4 flex items-center text-sm text-text-muted">
                <span>Estimated from active plans</span>
            </div>
        </div>

        <!-- Stat Card 4: Action Items (Placeholder for now) -->
        <div class="card">
            <h3 class="text-sm font-medium text-text-muted">System Status</h3>
            <p class="text-lg font-bold text-success mt-2">Healthy</p>
            <div class="mt-4 flex items-center text-sm text-text-muted">
                <span>All systems operational</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity / Business Table -->
    <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-text">Recent Signups</h3>
            <a href="{{ route('internal.tenants.index') }}" class="text-sm text-primary hover:text-blue-700 font-medium">View All</a>
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
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Joined
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentBusinesses as $business)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-text font-bold">
                                        {{ substr($business->name, 0, 1) }}</div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-text">{{ $business->name }}</div>
                                        <div class="text-xs text-text-muted">ID: {{ $business->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $sub = $business->subscriptions->first(); @endphp
                                @if($sub && $sub->plan)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $sub->plan->name }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No Plan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $sub = $business->subscriptions->first(); @endphp
                                @if($sub && $sub->isActive())
                                     <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                     <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                {{ $business->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-text-muted">
                                No businesses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection