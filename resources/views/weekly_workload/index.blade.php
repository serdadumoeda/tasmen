<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analisis Beban Kerja Mingguan Tim') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    <p class="text-base text-gray-700 mb-6 flex items-center"> {{-- Menyesuaikan ukuran teks dan ikon --}}
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
                            <thead class="bg-gray-100"> {{-- Header tabel lebih menonjol --}}
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
                            <tbody class="bg-white divide-y divide-gray-100"> {{-- Divider lebih halus --}}
                                @forelse ($teamMembers as $member)
                                @php
                                    // Lakukan kalkulasi persentase di sini, karena data sudah di-eager load
                                    $assignedHours = $member->total_assigned_hours ?? 0;
                                    $workloadPercentage = ($standardHours > 0)
                                        ? ($assignedHours / $standardHours) * 100
                                        : 0;
                                    $workloadPercentage = round($workloadPercentage);
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 flex items-center">
                                        <i class="fas fa-user-tag mr-2 text-gray-500"></i> {{ $member->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="font-semibold">{{ number_format($assignedHours, 1) }}</span> jam
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-4 mr-4 shadow-inner"> {{-- Tinggi progress bar lebih besar, shadow-inner --}}
                                                @php
                                                    $bgColor = 'bg-blue-500'; // Default
                                                    if ($workloadPercentage > 100) {
                                                        $bgColor = 'bg-red-500'; // Overload
                                                    } elseif ($workloadPercentage > 70) {
                                                        $bgColor = 'bg-orange-500'; // High but not overload
                                                    } elseif ($workloadPercentage < 50) {
                                                        $bgColor = 'bg-green-500'; // Underload
                                                    }
                                                @endphp
                                                <div class="{{ $bgColor }} h-4 rounded-full transition-all duration-300 ease-in-out" style="width: {{ min($workloadPercentage, 100) }}%"></div>
                                            </div>
                                            <span class="font-bold text-sm {{ $workloadPercentage > 100 ? 'text-red-700' : ($workloadPercentage < 70 ? 'text-green-700' : 'text-orange-700') }}">
                                                {{ $workloadPercentage }}%
                                            </span>
                                            <span class="ml-2 inline-flex items-center">
                                                @if ($workloadPercentage > 100)
                                                    <i class="fas fa-exclamation-circle text-red-500" title="Beban Berlebih!"></i>
                                                @elseif ($workloadPercentage < 70)
                                                    <i class="fas fa-check-circle text-green-500" title="Beban Kerja Rendah"></i>
                                                @else
                                                    <i class="fas fa-info-circle text-blue-500" title="Beban Kerja Normal"></i>
                                                @endif
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