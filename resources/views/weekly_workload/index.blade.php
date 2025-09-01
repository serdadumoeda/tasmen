<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja Mingguan Tim') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-base text-gray-700 mb-6 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-blue-500 fa-lg"></i>
                        Halaman ini menganalisis total jam kerja yang ditugaskan kepada setiap anggota tim dibandingkan dengan standar
                        <strong class="text-indigo-600 ml-1">{{ $standardHours }} jam per minggu</strong>.
                    </p>

                    <!-- Form Pencarian -->
                    <div class="mb-6">
                        <form action="{{ route('weekly-workload.index') }}" method="GET">
                            <div class="relative">
                                <input type="text" name="search" placeholder="Cari nama anggota tim..." value="{{ $search ?? '' }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">
                                        <i class="fas fa-user-circle mr-2"></i> Nama Anggota
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-hourglass-start mr-2"></i> Total Jam Ditugaskan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">
                                        <i class="fas fa-chart-line mr-2"></i> Beban Kerja Mingguan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($workloadData as $data)
                                @php
                                    $workloadRatio = $data['workload_ratio'];
                                    $workloadPercentage = round($workloadRatio * 100);

                                    $bgColor = 'bg-red-500'; // Overload
                                    $textColor = 'text-red-700';
                                    $icon = 'fas fa-exclamation-circle text-red-500';
                                    $title = 'Beban Berlebih!';

                                    if ($workloadRatio <= $thresholdNormal) {
                                        $bgColor = 'bg-green-500'; // Normal
                                        $textColor = 'text-green-700';
                                        $icon = 'fas fa-check-circle text-green-500';
                                        $title = 'Beban Kerja Normal';
                                    } elseif ($workloadRatio <= $thresholdWarning) {
                                        $bgColor = 'bg-yellow-500'; // Warning
                                        $textColor = 'text-yellow-700';
                                        $icon = 'fas fa-info-circle text-yellow-500';
                                        $title = 'Beban Kerja Penuh';
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 flex items-center">
                                        <i class="fas fa-user-tag mr-2 text-gray-500"></i> {{ $data['user']->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="font-semibold">{{ number_format($data['assigned_hours'], 1) }}</span> jam
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-4 mr-4 shadow-inner">
                                                <div class="{{ $bgColor }} h-4 rounded-full transition-all duration-300 ease-in-out" style="width: {{ min($workloadPercentage, 100) }}%"></div>
                                            </div>
                                            <span class="font-bold text-sm {{ $textColor }}">
                                                {{ $workloadPercentage }}%
                                            </span>
                                            <span class="ml-2 inline-flex items-center">
                                                <i class="{{ $icon }}" title="{{ $title }}"></i>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center p-10 text-gray-500 text-lg bg-gray-50 rounded-lg shadow-md">
                                        <i class="fas fa-users-slash fa-3x text-gray-400 mb-4"></i>
                                        <p>Tidak ada anggota tim yang cocok dengan pencarian Anda.</p>
                                        <p class="text-sm text-gray-400 mt-2">Coba gunakan kata kunci lain atau reset pencarian.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginasi -->
                    <div class="mt-8">
                        {{ $teamMembers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>