<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Hierarki Pengguna & Unit Kerja') }}
            </h2>
            <div>
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                    <i class="fas fa-list mr-2"></i> {{ __('Tampilan Daftar') }}
                </a>
                <a href="{{ route('users.create') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i> {{ __('Tambah Pengguna') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="javascript:history.back()" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                    <h1 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-network-wired mr-2 text-indigo-600"></i> Struktur Organisasi
                    </h1>

                    @forelse ($units as $unit)
                        @include('users.partials.unit-hierarchy-row', ['unit' => $unit, 'level' => 0, 'users' => $users])
                    @empty
                        <div class="px-6 py-8 text-center text-lg text-gray-500">
                            <i class="fas fa-building-circle-exclamation fa-3x text-gray-400 mb-4"></i>
                            <p>Tidak ada struktur unit yang dapat ditampilkan.</p>
                            @if(Auth::user()->isSuperAdmin())
                                <p class="text-sm text-gray-400 mt-2">
                                    Silakan tambahkan unit kerja baru melalui <a href="{{ route('admin.units.index') }}" class="text-indigo-600 hover:underline">Manajemen Unit</a>.
                                </p>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>