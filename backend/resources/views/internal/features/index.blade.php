@extends('internal.layouts.app')

@section('header', 'Features')

@section('actions')
    <a href="{{ route('internal.features.create') }}" class="btn-primary flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Feature
    </a>
@endsection

@section('content')
    <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Default
                            Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Created
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($features as $feature)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="ml-0">
                                        <div class="text-sm font-medium text-text">{{ $feature->name }}</div>
                                        <div class="text-sm text-text-muted">{{ $feature->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($feature->type) }}
                                    @if(!$feature->is_active)
                                        <span
                                            class="ml-2 inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Disabled</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text">
                                @if($feature->default_limit === -1)
                                    <span class="text-success font-medium">Unlimited</span>
                                @else
                                    {{ $feature->default_limit }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                {{ $feature->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('internal.features.edit', $feature->id) }}"
                                        class="text-primary hover:text-blue-700">Edit</a>

                                    <form method="POST" action="{{ route('internal.features.destroy', $feature->id) }}"
                                        onsubmit="return confirm('Delete this feature? This might affect existing plans.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-text-muted">
                                No features defined yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($features->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $features->links() }}
            </div>
        @endif
    </div>
@endsection