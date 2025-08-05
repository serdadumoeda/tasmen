<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Unit') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-sitemap mr-2 text-indigo-600"></i> Daftar Unit
                        </h1>
                        <a href="{{ route('admin.units.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Unit
                        </a>
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

                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">Nama</th>
                                    <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Level</th>
                                    <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Atasan</th>
                                    <th class="py-3 px-6 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($units as $unit)
                                    @include('admin.units.partials.unit-row', ['unit' => $unit, 'level' => 0])
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 whitespace-nowrap text-center text-lg text-gray-500">
                                            Tidak ada unit kerja yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>