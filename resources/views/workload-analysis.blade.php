<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja (Man-Hours & SK)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Beban Kerja per Personil</h3>
                    <p class="text-sm text-gray-600 mb-4">Analisis ini menggabungkan total jam dari tugas proyek aktif dengan jumlah penugasan non-proyek (SK) yang sedang berjalan.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personil</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Proyek Aktif</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisasi Mingguan (Proyek)</th>
                                    {{-- KOLOM BARU --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beban SK Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($userWorkload as $workload)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $workload['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $workload['role'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold">{{ $workload['active_tasks_count'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $utilization = $workload['utilization'];
                                                $barColor = $utilization > 100 ? 'bg-red-500' : ($utilization > 85 ? 'bg-yellow-500' : 'bg-green-500');
                                            @endphp
                                            <div class="w-full bg-gray-200 rounded-full h-4">
                                                <div class="{{ $barColor }} h-4 rounded-full text-center text-white text-xs" style="width: {{ min($utilization, 100) }}%">
                                                    {{ $utilization }}%
                                                </div>
                                            </div>
                                            <div class="text-xs text-center text-gray-500 mt-1">{{ $workload['total_assigned_hours'] }} jam teralokasi</div>
                                        </td>
                                        {{-- DATA BARU --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                {{ $workload['special_assignments_count'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data untuk dianalisis.</td>
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