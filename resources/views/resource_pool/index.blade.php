<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Resource Pool Cerdas') }}
            </h2>
            <a href="{{ route('resource-pool.workflow') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-project-diagram mr-2"></i> {{ __('Lihat Alur Kerja') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <p class="text-base text-gray-700 mb-6 flex items-center"> {{-- Menyesuaikan ukuran teks dan ikon --}}
                    <i class="fas fa-info-circle mr-3 text-blue-500 fa-lg"></i>
                    Halaman ini membantu Anda memilih anggota tim yang tersedia. Sistem secara otomatis merekomendasikan
                    <span class="inline-flex items-center ml-1 text-green-600 font-bold">
                        <i class="fas fa-check-circle mr-1"></i> Anggota (beban kerja &lt; 70%)
                    </span>.
                </p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100"> {{-- Header tabel lebih menonjol --}}
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-user-circle mr-2"></i> Nama Anggota
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-chart-bar mr-2"></i> Beban Kerja
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-notes-medical mr-2"></i> Catatan Ketersediaan
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-toggle-on mr-2"></i> Tersedia di Pool?
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100"> {{-- Divider lebih halus --}}
                            @forelse ($workloadData as $data)
                            <tr id="member-{{ $data['user']->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    {{ $data['user']->name }}
                                    <span class="ml-2 inline-flex items-center">
                                    @if ($data['workload_percentage'] < 70)
                                        <i class="fas fa-check-circle text-green-500" title="Direkomendasikan (beban kerja di bawah 70%)"></i>
                                    @elseif ($data['workload_percentage'] > 100)
                                        <i class="fas fa-exclamation-triangle text-red-500" title="Beban Berlebih (di atas 100%)"></i>
                                    @else
                                        <i class="fas fa-info-circle text-blue-500" title="Beban kerja normal (70-100%)"></i>
                                    @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="font-semibold {{ $data['workload_percentage'] > 100 ? 'text-red-600' : ($data['workload_percentage'] < 70 ? 'text-green-600' : 'text-orange-600') }}">
                                        {{ $data['workload_percentage'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" class="notes-input block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 text-sm"
                                           data-member-id="{{ $data['user']->id }}"
                                           value="{{ $data['user']->pool_availability_notes }}"
                                           placeholder="Catatan ketersediaan...">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{-- Toggle Switch Modern --}}
                                    <label for="poolSwitch{{ $data['user']->id }}" class="flex items-center cursor-pointer justify-center">
                                        <div class="relative">
                                            <input type="checkbox" id="poolSwitch{{ $data['user']->id }}" class="sr-only pool-toggle"
                                                   data-member-id="{{ $data['user']->id }}"
                                                   {{ $data['user']->is_in_resource_pool ? 'checked' : '' }}>
                                            <div class="block bg-gray-300 w-14 h-8 rounded-full transition-all duration-300"></div> {{-- Base --}}
                                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-all duration-300 transform"
                                                 :class="{ 'translate-x-full bg-indigo-600': document.getElementById('poolSwitch{{ $data['user']->id }}').checked, 'bg-white': !document.getElementById('poolSwitch{{ $data['user']->id }}').checked }"
                                                 style="--tw-translate-x: 0;"></div> {{-- Dot --}}
                                        </div>
                                    </label>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center p-10 text-gray-500 text-lg bg-gray-50 rounded-lg shadow-md">
                                    <i class="fas fa-users-slash fa-3x text-gray-400 mb-4"></i>
                                    <p>Tidak ada anggota tim untuk dikelola dalam resource pool.</p>
                                    <p class="text-sm text-gray-400 mt-2">Pastikan ada pengguna yang terdaftar di sistem Anda.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>