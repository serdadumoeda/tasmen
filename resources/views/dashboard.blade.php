<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Proyek') }}
            </h2>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Buat Proyek Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notifikasi Sukses --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Daftar Proyek Anda</h3>
                    <div class="space-y-4">
                        @forelse ($projects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                                <div class="flex justify-between">
                                    <p class="font-bold text-blue-600">{{ $project->name }}</p>
                                    <p class="text-sm text-gray-600">Ketua Tim: {{ $project->leader->name }}</p>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">{{ \Illuminate\Support\Str::limit($project->description, 100) }}</p>
                            </a>
                        @empty
                            <p>Anda belum memiliki proyek. Silakan buat yang baru!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>