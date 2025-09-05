<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Unit') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-sitemap mr-2 text-indigo-600"></i> Daftar Unit
                    </h1>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.units.workflow') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                            <i class="fas fa-project-diagram mr-2"></i> Lihat Alur Kerja
                        </a>
                        <a href="{{ route('admin.units.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Unit
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-500"></i>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <div class="space-y-2">
                     <div class="flex items-center bg-gray-100 p-3 rounded-lg shadow-sm font-semibold text-gray-600 text-xs uppercase">
                        <div class="w-1/3">Nama Unit</div>
                        <div class="w-1/4">Kepala Unit</div>
                        <div class="w-1/4">Unit Atasan</div>
                        <div class="w-1/6 text-right">Aksi</div>
                    </div>
                    @forelse ($units as $unit)
                        @include('admin.units.partials._unit-tree-item', ['unit' => $unit, 'level' => 0])
                    @empty
                        <div class="text-center text-gray-500 py-8">
                            Tidak ada unit kerja yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>