@extends('internal.layouts.app')

@section('header', 'Leads')

@section('content')
    <div class="bg-surface shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Lead
                            Details
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Message
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-text-muted uppercase tracking-wider">Status &
                            Date
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-text-muted uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leads as $lead)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-text">{{ $lead->name }}</div>
                                <div class="text-sm text-text-muted mb-1"><a href="mailto:{{ $lead->email }}"
                                        class="hover:underline hover:text-blue-600">{{ $lead->email }}</a></div>
                                <div class="text-xs text-gray-500 mt-1 flex gap-3">
                                    @if ($lead->phone)
                                        <span class="inline-block" title="Phone">
                                            📞 {{ $lead->country_code }} {{ $lead->phone }}
                                        </span>
                                    @endif
                                    @if ($lead->whatsapp_number)
                                        <span class="inline-block text-green-600" title="WhatsApp">
                                            💬 {{ $lead->whatsapp_number }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-text-muted w-1/3" title="{{ $lead->message }}">
                                <div class="whitespace-normal line-clamp-3">{{ $lead->message ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($lead->status === 'new') bg-blue-100 text-blue-800
                                    @elseif($lead->status === 'contacted') bg-yellow-100 text-yellow-800
                                    @elseif($lead->status === 'converted') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($lead->status) }}
                                </span>
                                <div class="text-xs text-text-muted mt-2">{{ $lead->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $lead->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('internal.leads.show', $lead) }}"
                                    class="text-blue-600 hover:text-blue-900 transition-colors border border-blue-200 bg-blue-50 px-3 py-1.5 rounded-md hover:bg-blue-100">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-text-muted">
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
