<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('API Key Management') }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.api_keys.workflow') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                    <i class="fas fa-project-diagram mr-2"></i>
                    Alur Kerja
                </a>
                <a href="{{ route('admin.api_keys.docs') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                    <i class="fas fa-book-open mr-2"></i>
                    Panduan Penggunaan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Session Messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Display New API Key --}}
            @if (session('newApiKey'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">New API Key Generated!</strong>
                    <p class="block sm:inline">Please copy this key. It will not be shown again.</p>
                    <div class="mt-2 p-2 bg-gray-100 border rounded">
                        <code>{{ session('newApiKey') }}</code>
                    </div>
                </div>
            @endif

            {{-- Create New API Client Form --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900">Create New API Client</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Create a new client to generate API keys for an external system.
                    </p>
                    <form method="POST" action="{{ route('admin.api_keys.store') }}" class="mt-6 space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Client Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Create Client') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- List of API Clients --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Existing API Clients</h3>
                <div class="space-y-4">
                    @forelse ($clients as $client)
                        <div class="border rounded-lg p-4 {{ !$client->is_active ? 'bg-gray-100 opacity-70' : '' }}">
                            <div class="flex justify-between items-center">
                                <h4 class="font-semibold text-lg">{{ $client->name }}</h4>
                                <div class="flex items-center space-x-4">
                                    {{-- Status Toggle --}}
                                    <form method="POST" action="{{ route('admin.api_keys.status.update', $client) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $client->is_active ? '0' : '1' }}">
                                        <button type="submit" class="text-sm font-medium {{ $client->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}">
                                            {{ $client->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    {{-- Delete Client --}}
                                    <form method="POST" action="{{ route('admin.api_keys.destroy', $client) }}" onsubmit="return confirm('Are you sure you want to delete this client and all its keys?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900">Delete Client</button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">Status: <span class="font-bold {{ $client->is_active ? 'text-green-600' : 'text-red-600' }}">{{ $client->is_active ? 'Active' : 'Inactive' }}</span></p>

                            <hr class="my-4">

                            {{-- Token Management --}}
                            <div>
                                <h5 class="font-medium">API Keys</h5>
                                <div class="mt-2 space-y-2">
                                    @forelse ($client->tokens as $token)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <div>
                                                <p class="text-sm font-mono">
                                                    <span class="font-semibold">{{ $token->name }}</span>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    <strong class="font-semibold">Hak Akses:</strong>
                                                    @if(empty($token->abilities) || in_array('*', $token->abilities))
                                                        <span class="italic">Akses Penuh</span>
                                                    @else
                                                        {{ implode(', ', $token->abilities) }}
                                                    @endif
                                                    |
                                                    Created: {{ $token->created_at->format('Y-m-d') }}
                                                </p>
                                            </div>
                                            <form method="POST" action="{{ route('admin.api_keys.tokens.destroy', ['client' => $client, 'tokenId' => $token->id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-900">Revoke</button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">No active API keys for this client.</p>
                                    @endforelse
                                </div>

                                {{-- Generate New Token Form --}}
                                <form method="POST" action="{{ route('admin.api_keys.tokens.store', $client) }}" class="mt-6">
                                    @csrf
                                    <div class="mb-4">
                                        <h6 class="font-medium text-sm text-gray-700">Pilih Hak Akses (Scopes)</h6>
                                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($availableScopes as $scope => $description)
                                                <label class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50">
                                                    <input type="checkbox" name="scopes[]" value="{{ $scope }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                    <span>{{ $description }} <span class="text-xs text-gray-500">({{ $scope }})</span></span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Jika tidak ada yang dipilih, kunci akan memiliki akses penuh.</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <x-primary-button>Generate New Key</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p>No API clients have been created yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
