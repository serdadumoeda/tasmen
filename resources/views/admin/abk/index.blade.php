<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja (ABK)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ABK Dashboard --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Dashboard Kebutuhan Pegawai</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pekerjaan/Jabatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam Dibutuhkan / Tahun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kebutuhan Pegawai (Orang)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($results as $result)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $result['job_type']->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($result['total_hours'], 2) }} jam</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-600">{{ number_format($result['employee_requirement'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.abk.show', $result['job_type']) }}" class="text-indigo-600 hover:text-indigo-900">Detail & Kelola Komponen</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500">Belum ada jenis pekerjaan yang ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add New Job Type Form --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Jenis Pekerjaan Baru</h3>
                <form action="{{ route('admin.abk.job-types.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Jenis Pekerjaan/Jabatan</label>
                            <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Tambah Jenis Pekerjaan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
