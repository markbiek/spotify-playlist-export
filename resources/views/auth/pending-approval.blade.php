<x-guest-layout>
    <div class="text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Registration Successful!') }}</h1>

        <div class="text-gray-600 mb-6">
            <p class="mb-4">{{ __('Thank you for registering! Your account has been created successfully.') }}</p>
            <p class="mb-4">{{ __('However, your account is currently pending admin approval. You will not be able to log in until an administrator has approved your account.') }}</p>
            <p>{{ __('Please wait for approval before attempting to log in. You will receive access once approved.') }}</p>
	    <p>{{ __('Email tech@antelopelovefan.com with questions.') }}</p>
        </div>

        <div class="flex justify-center">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Go to Login Page') }}
            </a>
        </div>
    </div>
</x-guest-layout>
