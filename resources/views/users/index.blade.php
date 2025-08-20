<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Tim') }}
            </h2>
            <div>
                <a href="{{ route('users.hierarchy') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                    <i class="fas fa-sitemap mr-2"></i> {{ __('Tampilan Hirarki') }}
                </a>
                <a href="{{ route('users.archived') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-archive mr-2"></i> {{ __('Lihat Arsip') }}
                </a>
                <a href="{{ route('admin.users.import.show') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-file-import mr-2"></i> {{ __('Impor Pengguna') }}
                </a>
                <a href="{{ route('users.create') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i> {{ __('Tambah Pengguna') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-xl mb-6">
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="flex items-center space-x-3">
                        <input type="text" name="search" placeholder="Cari nama atau email..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-lg border-gray-300 text-sm py-2 px-3 transition duration-150" value="{{ request('search') }}">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150 transform hover:scale-105">
                            <i class="fas fa-search mr-2"></i> {{ __('Cari') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-redo mr-2"></i> {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

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
                                <i class="fas fa-user-tie mr-2"></i> {{ __('Atasan Langsung') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-building mr-2"></i> {{ __('Unit') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3 text-center rounded-tr-lg"><span class="sr-only">{{ __('Aksi') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-indigo-600 font-semibold">{{ $user->jabatan->name ?? 'Jabatan belum diatur' }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $role = $user->role;
                                        $badgeColor = match ($role) {
                                            'Superadmin', 'Menteri' => 'bg-red-100 text-red-800',
                                            'Eselon I', 'Eselon II' => 'bg-indigo-100 text-indigo-800',
                                            'Koordinator', 'Sub Koordinator' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeColor }}">
                                        {{ $role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-800">{{ $user->atasan->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->atasan->jabatan->name ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 flex items-center">
                                        <i class="fas fa-building-user mr-2 text-gray-500"></i> {{ $user->unit->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('users.show', $user) }}" class="text-green-600 hover:text-green-900 inline-flex items-center p-2 rounded-full hover:bg-green-50 transition-colors duration-200" title="{{ __('Lihat Profil') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(Auth::user()->isSuperAdmin() && Auth::id() !== $user->id && !$user->isSuperAdmin())
                                        <a href="{{ route('admin.users.impersonate', $user) }}" class="text-cyan-600 hover:text-cyan-900 inline-flex items-center p-2 rounded-full hover:bg-cyan-50 transition-colors duration-200 ml-2" title="{{ __('Tiru Pengguna Ini') }}">
                                            <i class="fas fa-user-secret"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center p-2 rounded-full hover:bg-indigo-50 transition-colors duration-200 ml-2" title="{{ __('Edit Pengguna') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('users.deactivate', $user) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin mengarsipkan pengguna ini?') }}');">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900 inline-flex items-center p-2 rounded-full hover:bg-yellow-50 transition-colors duration-200" title="{{ __('Arsipkan Pengguna') }}">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 whitespace-nowrap text-center text-lg text-gray-500 bg-gray-50 rounded-lg shadow-md">
                                    <i class="fas fa-users-slash fa-3x text-gray-400 mb-4"></i>
                                    <p>{{ __('Tidak ada pengguna ditemukan.') }}</p>
                                    <p class="text-sm text-gray-400 mt-2">{{ __('Coba sesuaikan filter pencarian atau tambahkan pengguna baru.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>