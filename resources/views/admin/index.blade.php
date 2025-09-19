<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin</h1>

                    <div class="mb-8">
                        <h2 class="text-lg font-semibold mb-4">Users Pending Approval</h2>

                        @if($pendingUsers->isEmpty())
                            <p class="text-gray-600">No users are waiting for approval.</p>
                        @else
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">{{ __('Name') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Email') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Registered') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingUsers as $user)
                                        <tr class="bg-white border-b">
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                                            <td class="px-6 py-4">{{ $user->email }}</td>
                                            <td class="px-6 py-4">{{ $user->created_at->diffForHumans() }}</td>
                                            <td class="px-6 py-4">
                                                <form method="POST" action="{{ route('admin.approve', $user) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                        {{ __('Approve') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>