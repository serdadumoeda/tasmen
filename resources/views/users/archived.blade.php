<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Arsip Pengguna') }}
            </h2>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('Kembali ke Daftar Pengguna') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">
                                <i class="fas fa-user mr-2"></i> {{ __('Pengguna') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-user-shield mr-2"></i> {{ __('Role') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-building mr-2"></i> {{ __('Unit Terakhir') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3 text-center rounded-tr-lg"><span class="sr-only">{{ __('Aksi') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($archivedUsers as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-800">{{ $user->role }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 flex items-center">
                                        <i class="fas fa-building-user mr-2 text-gray-500"></i> {{ $user->unit->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <form action="{{ route('users.reactivate', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Aktifkan kembali pengguna ini?') }}');">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900 inline-flex items-center p-2 rounded-full hover:bg-green-50 transition-colors duration-200" title="{{ __('Aktifkan Kembali') }}">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 whitespace-nowrap text-center text-lg text-gray-500 bg-gray-50 rounded-lg shadow-md">
                                    <i class="fas fa-archive fa-3x text-gray-400 mb-4"></i>
                                    <p>{{ __('Tidak ada pengguna yang diarsipkan.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $archivedUsers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
