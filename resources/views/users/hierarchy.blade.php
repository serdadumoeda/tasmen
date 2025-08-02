<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hierarki Pengguna & Unit Kerja') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                    <h1 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-network-wired mr-2 text-indigo-600"></i> Struktur Organisasi
                    </h1>

                    @forelse ($units as $unit)
                        @include('users.partials.unit-hierarchy-row', ['unit' => $unit, 'level' => 0])
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