<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Komponen Beban Kerja untuk: ') }} <span class="text-indigo-600">{{ $jobType->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Component List --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Daftar Komponen Beban Kerja</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uraian Pekerjaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume / Tahun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Norma Waktu (Jam)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($jobType->workloadComponents as $component)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $component->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $component->volume }} {{ $component->output_unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $component->time_norm }} jam</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ number_format($component->volume * $component->time_norm, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500">Belum ada komponen yang ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add New Component Form --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Komponen Baru</h3>
                <form action="{{ route('admin.abk.components.store', $jobType) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Uraian Pekerjaan</label>
                            <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="volume" class="block text-sm font-medium text-gray-700">Volume / Tahun</label>
                            <input type="number" name="volume" id="volume" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="output_unit" class="block text-sm font-medium text-gray-700">Satuan Output</label>
                            <input type="text" name="output_unit" id="output_unit" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., Dokumen">
                        </div>
                        <div>
                            <label for="time_norm" class="block text-sm font-medium text-gray-700">Norma Waktu (Jam)</label>
                            <input type="number" step="0.01" name="time_norm" id="time_norm" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., 1.5">
                        </div>
                    </div>
                    <div class="flex items-end justify-end mt-6">
                        <button type="submit" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Tambah Komponen
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
