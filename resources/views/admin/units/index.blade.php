<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Unit') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6"> {{-- Margin bawah lebih besar --}}
                        <h1 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-sitemap mr-2 text-indigo-600"></i> Daftar Unit
                        </h1>
                        <a href="{{ route('admin.units.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Tambah Unit modern --}}
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Unit
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert"> {{-- Styling alert konsisten --}}
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm"> {{-- Border pada tabel, rounded-lg, shadow-sm --}}
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100"> {{-- Header tabel lebih menonjol --}}
                                <tr>
                                    <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">
                                        <i class="fas fa-building mr-2"></i> Nama
                                    </th>
                                    <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-stairs mr-2"></i> Level
                                    </th>
                                    <th class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-building-circle-arrow-up mr-2"></i> Unit Atasan
                                    </th>
                                    <th class="py-3 px-6 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">
                                        <i class="fas fa-tools mr-2"></i> Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100"> {{-- Divider lebih halus --}}
                                @forelse($units as $unit)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150"> {{-- Hover effect pada baris --}}
                                        <td class="py-2 px-4 border-b text-sm text-gray-900 font-medium">{{ $unit->name }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-700">{{ $unit->level }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-700">{{ $unit->parentUnit->name ?? '-' }}</td>
                                        <td class="py-2 px-4 border-b text-center">
                                            <a href="{{ route('admin.units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center p-2 rounded-full hover:bg-indigo-50 transition-colors duration-200" title="Edit Unit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded-full hover:bg-red-50 transition-colors duration-200" title="Hapus Unit">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 whitespace-nowrap text-center text-lg text-gray-500 bg-gray-50 rounded-lg shadow-md">
                                            <i class="fas fa-building-circle-exclamation fa-3x text-gray-400 mb-4"></i>
                                            <p>Tidak ada unit kerja yang terdaftar.</p>
                                            <p class="text-sm text-gray-400 mt-2">Silakan tambahkan unit kerja baru.</p>
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