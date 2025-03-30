<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-700">Staff Account Registration</h2>
        <p class="text-gray-500">Create an account with your staff credentials</p>
    </div>

    <form method="POST" action="{{ route('staff.register') }}">
        @csrf

        <!-- Staff Selection -->
        <div>
            <x-input-label for="staff_id" :value="__('Select Your Staff Record')" />
            <select id="staff_id" name="staff_id" class="block mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">-- Select Staff --</option>
                @foreach($staffWithoutAccounts as $staff)
                    <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }} ({{ $staff->email }}) - {{ $staff->getTypeLabelAttribute() }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('staff.login') }}">
                {{ __('Already have an account?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> 