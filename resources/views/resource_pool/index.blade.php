<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Resource Pool Cerdas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- PERBAIKAN: Ubah teks deskripsi --}}
                    <p class="text-gray-600 mb-4">
                        Halaman ini membantu Anda memilih anggota tim yang tersedia. Sistem secara otomatis merekomendasikan (<i class="fas fa-check-circle text-green-500"></i>) anggota dengan **beban kerja di bawah 70%**.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beban Kerja</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan Ketersediaan</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tersedia di Pool?</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($workloadData as $data)
                                <tr id="member-{{ $data['user']->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium">
                                        {{ $data['user']->name }}

                                        {{-- PERBAIKAN: Ubah kondisi untuk rekomendasi --}}
                                        @if ($data['workload_percentage'] < 70)
                                            <i class="fas fa-check-circle text-green-500 ml-2" title="Direkomendasikan (beban kerja di bawah 70%)"></i>
                                        @elseif ($data['workload_percentage'] > 100)
                                            <i class="fas fa-exclamation-triangle text-red-500 ml-2" title="Beban Berlebih (di atas 100%)"></i>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold">{{ $data['workload_percentage'] }}%</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" class="notes-input block w-full rounded-md shadow-sm border-gray-300 sm:text-sm"
                                               data-member-id="{{ $data['user']->id }}"
                                               value="{{ $data['user']->pool_availability_notes }}"
                                               placeholder="Catatan ketersediaan...">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{-- Toggle Switch --}}
                                        <label for="poolSwitch{{ $data['user']->id }}" class="flex items-center cursor-pointer justify-center">
                                            <div class="relative">
                                                <input type="checkbox" id="poolSwitch{{ $data['user']->id }}" class="sr-only pool-toggle"
                                                       data-member-id="{{ $data['user']->id }}"
                                                       {{ $data['user']->is_in_resource_pool ? 'checked' : '' }}>
                                                <div class="block bg-gray-200 w-14 h-8 rounded-full"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                                            </div>
                                        </label>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center p-4">Tidak ada anggota tim untuk dikelola.</td>
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