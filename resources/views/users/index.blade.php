<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Management') }}
            </h2>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Tambah User Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- 
                Struktur tabel diubah menjadi div untuk mengakomodasi daftar bersarang (nested list).
                Setiap user level atas akan dipanggil dari sini, dan partial view akan mengurus sisanya.
                --}}
                <div class="divide-y divide-gray-200">
                    @forelse ($topLevelUsers as $user)
                        {{-- Memanggil partial view rekursif untuk pertama kalinya dengan level 0 --}}
                        @include('users.partials.user-hierarchy-row', ['user' => $user, 'level' => 0])
                    @empty
                        <p class="p-6 text-gray-500">Tidak ada user yang dapat Anda kelola.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>