@extends('internal.layouts.app')

@section('header', 'Leads')

@section('content')
    <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Name
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Email
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Phone
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Message
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
                    @forelse($leads as $lead)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-text">{{ $lead->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">{{ $lead->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">{{ $lead->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-text-muted max-w-xs truncate" title="{{ $lead->message }}">
                                {{ Str::limit($lead->message, 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                {{ $lead->created_at->format('M d, Y h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-text-muted">
                                No leads found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leads->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
@endsection
