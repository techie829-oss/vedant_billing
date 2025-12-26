@extends('internal.layouts.app')

@section('header')
    <div class="flex items-center">
        <a href="{{ route('internal.users.index') }}" class="mr-4 text-text-muted hover:text-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        Add Team Member
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-surface shadow-sm border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ route('internal.users.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-text-muted mb-1">Full Name</label>
                        <input type="text" name="name" id="name" class="input-field" required value="{{ old('name') }}">
                        @error('name') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-text-muted mb-1">Email Address</label>
                        <input type="email" name="email" id="email" class="input-field" required value="{{ old('email') }}">
                        @error('email') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-text-muted mb-1">Role</label>
                        <select name="role" id="role" class="input-field">
                            @foreach(\App\Enums\InternalRole::cases() as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-text-muted mb-1">Initial
                            Password</label>
                        <input type="password" name="password" id="password" class="input-field" required>
                        @error('password') <p class="text-sm text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary">Create Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection