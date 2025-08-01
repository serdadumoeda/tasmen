<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Tim - Tampilan Hirarki') }}
            </h2>
            <div>
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105"> {{-- Tombol Tampilan Daftar modern --}}
                    <i class="fas fa-list mr-2"></i> {{ __('Tampilan Daftar') }}
                </a>
                <a href="{{ route('users.create') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Tambah Pengguna modern --}}
                    <i class="fas fa-user-plus mr-2"></i> {{ __('Tambah Pengguna') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="text-base text-gray-700 mb-6 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-blue-500 fa-lg"></i>
                        Gunakan tampilan hirarki ini untuk melihat struktur tim dan bawahan setiap pengguna.
                    </p>
                    <div class="space-y-4"> {{-- Spasi antar kartu hirarki --}}
                        @forelse($users as $user)
                            {{-- Setiap user level atas akan memulai rantai rekursifnya sendiri --}}
                            @include('users.partials.user-hierarchy-row', ['user' => $user, 'level' => 0])
                        @empty
                             <div class="text-center text-gray-500 p-10 bg-gray-50 rounded-lg shadow-md">
                                <i class="fas fa-users-slash fa-3x text-gray-400 mb-4"></i>
                                <p class="text-lg">Tidak ada pengguna untuk ditampilkan dalam hirarki.</p>
                                <p class="text-sm text-gray-400 mt-2">Pastikan ada pengguna yang terdaftar dan memiliki relasi atasan/bawahan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>