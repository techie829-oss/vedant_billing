@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.tenants.index') }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        {{ $business->name }}
    </div>
@endsection

@section('actions')
    <a href="{{ route('internal.tenants.edit', $business->id) }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
        Edit
    </a>

    <form method="POST" action="{{ route('internal.tenants.update-status', $business->id) }}" class="inline-block">
        @csrf
        @method('PATCH')
        @if($business->status === 'active')
            <input type="hidden" name="status" value="suspended">
            <button type="submit"
                class="px-4 py-2 bg-red-50 text-red-700 rounded-md hover:bg-red-100 font-medium text-sm border border-red-200"
                onclick="return confirm('Are you sure you want to suspend this business?')">
                Suspend Business
            </button>
        @else
            <input type="hidden" name="status" value="active">
            <button type="submit"
                class="px-4 py-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 font-medium text-sm border border-green-200">
                Activate Business
            </button>
        @endif
    </form>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Info Card -->
            <div class="card">
                <h3 class="text-lg font-medium text-text mb-4">Business Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-text-muted">Slug (ID)</dt>
                        <dd class="mt-1 text-sm text-text font-mono bg-gray-50 px-2 py-1 rounded inline-block">
                            {{ $business->slug }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-text-muted">Status</dt>
                        <dd class="mt-1">
                            @if($business->status === 'active')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ ucfirst($business->status) }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-text-muted">Joined</dt>
                        <dd class="mt-1 text-sm text-text">{{ $business->created_at->format('M d, Y, h:i A') }}</dd>
                        <dd class="mt-1 text-sm text-text">{{ $business->created_at->format('M d, Y, h:i A') }}</dd>
                    </div>

                    <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2"></div>

                    @if($business->mobile)
                        <div>
                            <dt class="text-sm font-medium text-text-muted">Mobile</dt>
                            <dd class="mt-1 text-sm text-text">{{ $business->mobile }}</dd>
                        </div>
                    @endif

                    @if($business->website)
                        <div>
                            <dt class="text-sm font-medium text-text-muted">Website</dt>
                            <dd class="mt-1 text-sm text-text">
                                <a href="{{ $business->website }}" target="_blank"
                                    class="text-primary hover:underline">{{ parse_url($business->website, PHP_URL_HOST) }}</a>
                            </dd>
                        </div>
                    @endif

                    @if($business->gstin)
                        <div>
                            <dt class="text-sm font-medium text-text-muted">GSTIN</dt>
                            <dd class="mt-1 text-sm text-text font-mono">{{ $business->gstin }}</dd>
                        </div>
                    @endif

                    @if($business->pan)
                        <div>
                            <dt class="text-sm font-medium text-text-muted">PAN</dt>
                            <dd class="mt-1 text-sm text-text font-mono">{{ $business->pan }}</dd>
                        </div>
                    @endif

                    @if($business->address)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-text-muted">Address</dt>
                            <dd class="mt-1 text-sm text-text whitespace-pre-line">{{ $business->address }}</dd>
                        </div>
                    @endif

                    @if($business->bank_name || $business->ifsc_code)
                        <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                            <dt class="text-sm font-medium text-text-muted mb-2">Bank Details</dt>
                            <dd class="text-sm text-text bg-gray-50 p-3 rounded border border-gray-200">
                                @if($business->bank_name)
                                <div class="font-medium">{{ $business->bank_name }}</div>@endif
                                @if($business->account_number)
                                <div>A/C: {{ $business->account_number }}</div>@endif
                                @if($business->ifsc_code)
                                <div>IFSC: {{ $business->ifsc_code }}</div>@endif
                            </dd>
                        </div>
                    @endif
            </div>

            <!-- Users List -->
            <div class="card">
                <h3 class="text-lg font-medium text-text mb-4">Users ({{ $business->users->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-0 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">
                                    Name</th>
                                <th
                                    class="px-0 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">
                                    Role</th>
                                <th
                                    class="px-0 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">
                                    Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($business->users as $user)
                                <tr>
                                    <td class="py-3 whitespace-nowrap text-sm text-text">
                                        <div class="font-medium">{{ $user->name }}</div>
                                        <div class="text-text-muted">{{ $user->email }}</div>
                                    </td>
                                    <td class="py-3 whitespace-nowrap text-sm text-text">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($user->pivot->role) }}
                                        </span>
                                    </td>
                                    <td class="py-3 whitespace-nowrap text-sm text-text-muted">
                                        {{ $user->pivot->joined_at ? $user->pivot->joined_at->format('M d, Y') : '-' }}
                                    </td>
                                    <td class="py-3 whitespace-nowrap text-sm text-right pr-4">
                                        <a href="{{ route('internal.tenants.users.password.edit', ['business' => $business->id, 'user' => $user->id]) }}"
                                            class="text-primary hover:text-blue-800 text-xs font-medium">
                                            Change Password
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Subscription Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-blue-50 border-blue-100">
                <h3 class="text-lg font-medium text-blue-900 mb-4">Current Subscription</h3>

                @if($sub = $business->subscriptions->first())
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-blue-700">Plan</div>
                            <div class="text-xl font-bold text-blue-900">{{ $sub->plan->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-blue-700">Billing Cycle</div>
                            <div class="text-sm font-medium text-blue-900">{{ ucfirst($sub->plan->interval) }}</div>
                        </div>

                        @if($sub->onTrial())
                            <div class="bg-blue-100 rounded p-2 text-sm text-blue-800">
                                On Trial until {{ $sub->trial_ends_at->format('M d') }}
                            </div>
                        @endif

                        <div class="pt-4 border-t border-blue-200">
                            <a href="{{ route('internal.subscriptions.create', ['business_id' => $business->id]) }}"
                                class="text-sm text-blue-700 hover:text-blue-900 font-medium hover:underline">
                                Upgrade / Change Plan
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-blue-800">No active subscription.</p>
                @endif
            </div>

            <!-- Feature Overrides -->
            <div class="card">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-text">Feature Overrides</h3>
                </div>

                @if($business->featureOverrides->count() > 0)
                    <div class="space-y-3 mb-6">
                        @foreach($business->featureOverrides as $override)
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded border border-gray-200">
                                <div>
                                    <div class="font-medium text-sm text-text">{{ $override->feature->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-text-muted">
                                        Limit:
                                        {{ $override->limit == -1 ? 'Unlimited' : ($override->limit == 0 ? 'Disabled' : $override->limit) }}
                                    </div>
                                </div>
                                <form method="POST"
                                    action="{{ route('internal.tenants.features.destroy', ['business' => $business->id, 'feature' => $override->feature_id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800"
                                        onclick="return confirm('Remove this override?')">Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-text-muted mb-6">No custom overrides configured.</p>
                @endif

                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-text mb-2">Add Override</h4>
                    <form method="POST" action="{{ route('internal.tenants.features.store', $business->id) }}"
                        class="space-y-3">
                        @csrf
                        <div>
                            <select name="feature_id" class="input-field text-sm py-1">
                                <option value="">Select Feature...</option>
                                @foreach($features as $feature)
                                    <option value="{{ $feature->id }}">{{ $feature->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <input type="number" name="limit" placeholder="Limit (-1=Unl, 0=Off)"
                                class="input-field text-sm py-1 flex-1" min="-1" required>
                            <button type="submit"
                                class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm font-medium text-text hover:bg-gray-50">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection