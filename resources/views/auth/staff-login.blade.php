<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-700">Staff Login Portal</h2>
        <p class="text-gray-500">Login with your staff credentials</p>
    </div>

    <form method="POST" action="{{ route('staff.login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Staff Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <div>
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('staff.register') }}">
                    {{ __("Don't have an account?") }}
                </a>
            </div>
            
            <div>
                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </div>
    </form>
    
    <div class="mt-6 p-4 border border-indigo-100 rounded-lg bg-indigo-50">
        <p class="text-sm text-indigo-700 mb-2">
            <strong>Staff Leaders:</strong> Department leaders have access to enhanced dashboard analytics and team oversight features.
        </p>
        <p class="text-xs text-indigo-600">
            Leaders can view detailed team statistics and weekend/holiday shift distributions.
        </p>
    </div>
    
    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">
            {{ __('Regular User Login') }}
        </a>
    </div>
</x-guest-layout> 