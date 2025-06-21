<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja (Man-Hours)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Beban Kerja per Personil</h3>
                    <p class="text-sm text-gray-600 mb-4">Analisis ini menghitung total jam dari tugas aktif (pending & in-progress) dan membandingkannya dengan kapasitas standar 40 jam per minggu.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personil</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Aktif</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam Dialokasikan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisasi Mingguan</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold">{{ $workload['total_assigned_hours'] }} jam</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $utilization = $workload['utilization'];
                                                $bgColor = 'bg-green-100';
                                                $barColor = 'bg-green-500';
                                                if ($utilization > 85) { $bgColor = 'bg-yellow-100'; $barColor = 'bg-yellow-500'; }
                                                if ($utilization > 100) { $bgColor = 'bg-red-100'; $barColor = 'bg-red-500'; }
                                            @endphp
                                            <div class="w-full {{ $bgColor }} rounded-full h-4">
                                                <div class="{{ $barColor }} h-4 rounded-full text-center text-white text-xs" style="width: {{ min($utilization, 100) }}%">
                                                    {{ $utilization }}%
                                                </div>
                                            </div>
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