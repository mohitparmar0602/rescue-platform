<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Register your agency to access the Rescue Coordination Platform.') }}
    </div>

    <form method="POST" action="{{ route('agency.register') }}" enctype="multipart/form-data">
        @csrf

        <!-- User Information Section -->
        <h3 class="text-lg font-medium text-gray-900 mt-6 mb-4">Admin Account Details</h3>
        
        <div>
            <x-input-label for="name" :value="__('Your Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Agency Information Section -->
        <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Agency Details</h3>

        <div class="mt-4">
            <x-input-label for="agency_name" :value="__('Agency Name')" />
            <x-text-input id="agency_name" class="block mt-1 w-full" type="text" name="agency_name" :value="old('agency_name')" required />
            <x-input-error :messages="$errors->get('agency_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="agency_type" :value="__('Agency Type')" />
            <select id="agency_type" name="agency_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="" disabled selected>Select an agency type</option>
                <option value="fire">Fire Department</option>
                <option value="police">Police Department</option>
                <option value="medical">Medical / EMS</option>
                <option value="ngo">NGO / Relief Organization</option>
                <option value="other">Other</option>
            </select>
            <x-input-error :messages="$errors->get('agency_type')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="registration_no" :value="__('Registration / License Number')" />
            <x-text-input id="registration_no" class="block mt-1 w-full" type="text" name="registration_no" :value="old('registration_no')" required />
            <x-input-error :messages="$errors->get('registration_no')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Headquarters Address')" />
            <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required>{{ old('address') }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>


        <div class="flex items-center justify-end mt-8">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register Agency') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
