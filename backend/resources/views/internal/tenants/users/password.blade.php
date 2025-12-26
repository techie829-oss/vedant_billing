@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.tenants.show', $business->id) }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Change Password: {{ $user->name }}
    </div>
@endsection

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg p-6">
            <div class="mb-4">
                <p class="text-sm text-text-muted">
                    Updating password for user <strong>{{ $user->email }}</strong> belonging to
                    <strong>{{ $business->name }}</strong>.
                </p>
            </div>

            <form method="POST"
                action="{{ route('internal.tenants.users.password.update', ['business' => $business->id, 'user' => $user->id]) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-text-muted mb-1">New Password</label>
                        <input type="password" name="password" id="password" class="input-field" required minlength="8"
                            placeholder="********">
                        @error('password') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-text-muted mb-1">Confirm
                            Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="input-field"
                            required minlength="8" placeholder="********">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary">Update Password</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection