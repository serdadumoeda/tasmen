<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja Mingguan Tim') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-gray-600 mb-4">
                        Halaman ini menganalisis total jam kerja yang ditugaskan kepada setiap anggota tim dibandingkan dengan standar
                        <strong>{{ $standardHours }} jam per minggu</strong>.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam Ditugaskan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beban Kerja Mingguan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($workloadData as $data)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $data['user']->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($data['assigned_hours'], 1) }} jam</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-4 mr-4">
                                                @php
                                                    $bgColor = $data['workload_percentage'] > 100 ? 'bg-red-500' : 'bg-blue-500';
                                                @endphp
                                                <div class="{{ $bgColor }} h-4 rounded-full" style="width: {{ min($data['workload_percentage'], 100) }}%"></div>
                                            </div>
                                            <span class="font-bold">{{ $data['workload_percentage'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center p-4">Tidak ada anggota tim untuk dianalisis.</td>
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