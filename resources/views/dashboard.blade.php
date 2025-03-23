<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($hasSpotifyToken && $userData)
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-xl font-semibold">{{ $userData['name'] }}</p>
                                <a href="{{ $userData['profile_url'] }}" target="_blank" class="text-sm text-gray-600 hover:text-gray-900">
                                    View Spotify Profile
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center">
                            <p class="mb-4">{{ __("We need to connect to your Spotify account.") }}</p>
                            <form method="POST" action="{{ route('spotify.connect') }}">
                                @csrf
                                <button type="submit" class="bg-[#1DB954] hover:bg-[#1ed760] text-black font-bold py-2 px-6 rounded border border-[#191414] shadow-sm">
                                    {{ __("Connect to Spotify") }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
