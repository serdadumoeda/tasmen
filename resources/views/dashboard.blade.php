<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Kegiatan') }}
            </h2>
            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create.step1') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Buat Kegiatan Baru') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Owned Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg mb-4">
                        {{ Auth::user()->isSuperAdmin() ? __('Semua Kegiatan') : __('Kegiatan yang Saya Buat') }}
                    </h3>
                    @if($ownedProjects->isEmpty())
                        <p>{{ __('Tidak ada kegiatan yang Anda buat.') }}</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($ownedProjects as $project)
                                <x-kanban-card :item="$project" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Member Projects -->
            @if(Auth::user()->isNotSuperAdmin() && $memberProjects->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-lg mb-4">{{ __('Kegiatan yang Saya Ikuti') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($memberProjects as $project)
                                <x-kanban-card :item="$project" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>