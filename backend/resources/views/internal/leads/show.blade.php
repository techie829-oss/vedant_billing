@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Lead: {{ $lead->name }}</h1>
        <a href="{{ route('leads.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
            &larr; Back to Leads
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Lead Information Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Lead Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Personal details and contact info.</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Full name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $lead->name }}</dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Email address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <a href="mailto:{{ $lead->email }}"
                                    class="text-blue-600 hover:underline">{{ $lead->email }}</a>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @if ($lead->phone)
                                    <a href="tel:{{ $lead->country_code }}{{ $lead->phone }}"
                                        class="text-blue-600 hover:underline">
                                        {{ $lead->country_code }} {{ $lead->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">WhatsApp</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @if ($lead->whatsapp_number)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp_number) }}"
                                        target="_blank" class="text-green-600 hover:underline flex items-center gap-1">
                                        {{ $lead->whatsapp_number }}
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Date Received</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $lead->created_at->format('F d, Y / h:i A') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white shadow border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Original Message</h3>
                </div>
                <div class="p-6 text-gray-700 whitespace-pre-wrap leading-relaxed">
                    {{ $lead->message ?: 'No message provided.' }}
                </div>
            </div>
        </div>

        <!-- Management Column -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow border border-gray-200 rounded-lg overflow-hidden sticky top-6">
                <form action="{{ route('leads.update', $lead) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Manage Status & Notes</h3>
                        <p class="mt-1 text-sm text-gray-500">Update the progress of this lead.</p>
                    </div>

                    <div class="p-6 space-y-6">
                        @if (session('success'))
                            <div
                                class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 text-sm font-medium">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Status Select -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Lead Status</label>
                            <select id="status" name="status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
                                <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                                <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted
                                </option>
                                <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted /
                                    Won</option>
                                <option value="closed" {{ $lead->status === 'closed' ? 'selected' : '' }}>Closed / Lost
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes Textarea -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                            <div class="mt-1">
                                <textarea id="notes" name="notes" rows="6"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Add follow-up notes, discussion points, etc...">{{ old('notes', $lead->notes) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">These notes are visible only to team members.</p>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button type="submit"
                            class="inline-flex justify-center flex-1 w-full sm:flex-none sm:w-auto py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Updates
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
