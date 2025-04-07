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

                        @if ($unfinishedExports->isEmpty())
                            <div class="text-center">
                                <p class="text-gray-600 pt-8 pb-4">{{ __("No playlist exports in progress.") }}</p>
                                <form method="POST" action="{{ route('playlists.export') }}">
                                    @csrf
                                    <button type="submit" class="bg-[#1DB954] hover:bg-[#1ed760] text-black font-bold py-2 px-6 rounded border border-[#191414] shadow-sm">
                                        {{ __("Start Export") }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold mb-4">{{ __("Exports in Progress") }}</h3>
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">{{ __("Started") }}</th>
                                            <th scope="col" class="px-6 py-3">{{ __("Progress") }}</th>
                                            <th scope="col" class="px-6 py-3">{{ __("Status") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unfinishedExports as $export)
                                            <tr class="bg-white border-b">
                                                <td class="px-6 py-4">{{ $export->created_at->diffForHumans() }}</td>
                                                <td class="px-6 py-4">
                                                    {{ $export->playlists_exported }} / {{ $export->playlist_count }}
                                                    ({{ number_format(($export->playlists_exported / max(1, $export->playlist_count)) * 100, 1) }}%)
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                                        {{ __("In Progress") }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if (!$completedExports->isEmpty())
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold mb-4">{{ __("Completed Exports") }}</h3>
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">{{ __("Date") }}</th>
                                            <th scope="col" class="px-6 py-3">{{ __("Playlists") }}</th>
                                            <th scope="col" class="px-6 py-3">{{ __("Actions") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($completedExports as $export)
                                            <tr class="bg-white border-b">
                                                <td class="px-6 py-4">{{ $export->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td class="px-6 py-4">{{ $export->playlist_count }}</td>
                                                <td class="px-6 py-4">
                                                    <form method="POST" action="{{ route('playlists.delete', $export) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('Are you sure you want to delete this export?') }}')">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
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
