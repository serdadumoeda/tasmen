<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <p class="text-4xl font-bold text-orange-500">{{ $globalStats['total_active_tasks'] }}</p>
                    <p class="text-gray-500 mt-1">Total Tugas Aktif (Pending & In Progress)</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <p class="text-4xl font-bold text-red-600">{{ $globalStats['total_overdue_tasks'] }}</p>
                    <p class="text-gray-500 mt-1">Total Tugas Overdue</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Beban Kerja per Pengguna</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Aktif</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Overdue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selesai (30 Hari)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor Beban Kerja</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($userWorkload as $workload)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $workload['name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold">{{ $workload['active_tasks_count'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-red-600">{{ $workload['overdue_tasks_count'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-green-600">{{ $workload['completed_last_30_days'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $score = $workload['workload_score'];
                                                $bgColor = 'bg-green-100 text-green-800'; // Rendah
                                                if ($score > 5) $bgColor = 'bg-yellow-100 text-yellow-800'; // Sedang
                                                if ($score > 10) $bgColor = 'bg-red-100 text-red-800'; // Tinggi
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $bgColor }}">
                                                {{ $score }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Beban Kerja per Proyek</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Aktif</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Overdue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penyelesaian</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($projectWorkload as $workload)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600">{{ $workload['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold">{{ $workload['active_tasks_count'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-red-600">{{ $workload['overdue_tasks_count'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $workload['member_count'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="bg-blue-600 h-4 rounded-full text-center text-white text-xs" style="width: {{ $workload['completion_percentage'] }}%">
                                                {{ $workload['completion_percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>