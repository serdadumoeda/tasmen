<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Alur Persetujuan (Workflow)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-xl font-bold text-gray-800">Daftar Alur Persetujuan</h1>
                        <a href="{{ route('admin.approval-workflows.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Alur Kerja Baru
                        </a>
                    </div>

                    <p class="text-gray-600 mb-4">
                        Ini adalah daftar alur persetujuan yang dapat diterapkan pada setiap unit kerja. Klik "Lihat Detail" untuk mengelola langkah-langkah (steps) persetujuan untuk setiap alur.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Alur Kerja</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($workflows as $workflow)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $workflow->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $workflow->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.approval-workflows.show', $workflow) }}" class="text-indigo-600 hover:text-indigo-900">Lihat Detail</a>
                                            <a href="{{ route('admin.approval-workflows.edit', $workflow) }}" class="text-yellow-600 hover:text-yellow-900 ml-4">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-8 text-gray-500">
                                            Belum ada alur kerja yang dibuat.
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
