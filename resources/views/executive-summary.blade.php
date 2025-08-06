<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ringkasan Eksekutif Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-gray-50 to-gray-200"> {{-- Latar belakang gradien lembut --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Panel Insight --}}
            <x-insight-panel :insights="$insights" :preview-insights="$previewInsights" />

            {{-- Bagian KPI Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- KPI: Proyek Aktif --}}
                <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-indigo-500"> {{-- Meningkatkan shadow --}}
                    <div class="text-indigo-600 mb-3 drop-shadow-md">
                        <i class="fas fa-folder-open fa-4x"></i>
                    </div>
                    <div class="text-5xl font-extrabold text-indigo-700">{{ $activeProjects }}</div>
                    <p class="text-sm text-gray-600 mt-2 font-semibold">Kegiatan Aktif</p>
                </div>
                {{-- KPI: Kegiatan Perlu Perhatian --}}
                <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-red-500"> {{-- Meningkatkan shadow --}}
                    <div class="text-red-600 mb-3 drop-shadow-md">
                        <i class="fas fa-exclamation-triangle fa-4x"></i>
                    </div>
                    <div class="text-5xl font-extrabold text-red-700">{{ $overdueProjectsCount }}</div>
                    <p class="text-sm text-gray-600 mt-2 font-semibold">Kegiatan Prioritas / Penting</p>
                </div>
                {{-- KPI: Penyerapan Anggaran --}}
                <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-green-500"> {{-- Meningkatkan shadow --}}
                    <div class="text-green-600 mb-3 drop-shadow-md">
                        <i class="fas fa-chart-line fa-4x"></i>
                    </div>
                    <div class="text-5xl font-extrabold text-green-700">{{ $budgetAbsorptionRate }}%</div>
                    <p class="text-sm text-gray-600 mt-2 font-semibold">Penyerapan Anggaran</p>
                </div>
                {{-- KPI: Progres Portofolio --}}
                <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-blue-500"> {{-- Meningkatkan shadow --}}
                    <div class="text-blue-600 mb-3 drop-shadow-md">
                        <i class="fas fa-tasks fa-4x"></i>
                    </div>
                    <div class="text-5xl font-extrabold text-blue-700">{{ $overallProgress }}%</div>
                    <p class="text-sm text-gray-600 mt-2 font-semibold">Progres Portofolio</p>
                </div>
            </div>

            {{-- Grafik Tren Kinerja --}}
            <div class="bg-white rounded-xl shadow-xl p-6 mb-8 lg:col-span-3 hover:shadow-2xl transition-shadow duration-300"> {{-- Meningkatkan shadow --}}
                <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-area mr-3 text-purple-600"></i> Tren Kinerja Portofolio (6 Bulan Terakhir)
                </h3>
                <div class="h-80"> {{-- Berikan tinggi eksplisit untuk chart --}}
                    <canvas id="performanceTrendChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Kolom Kiri: Daftar Proyek & Alokasi Anggaran --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Daftar Portofolio Kegiatan --}}
                    <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transition-shadow duration-300" x-data="{ open: true }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-xl text-gray-800 flex items-center">
                                <i class="fas fa-project-diagram mr-3 text-indigo-600"></i> Ringkasan Portofolio Kegiatan
                            </h3>
                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        </div>
                        <div class="overflow-x-auto" x-show="open" x-transition>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-lg">Nama Kegiatan</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Progres</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-lg">Anggaran</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse ($projectsForTable as $project)
                                        <tr class="hover:bg-blue-50 transition-colors duration-150 group">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-800 font-bold group-hover:underline">{{ $project->name }}</a>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-0.5">P.Jawab: {{ $project->leader->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $project->status_color_class }} shadow-sm">
                                                    {{ Str::title(str_replace('_', ' ', $project->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 font-medium">{{ $project->progress }}%</div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                    <div class="bg-blue-500 h-2 rounded-full shadow-inner" style="width: {{ $project->progress }}%"></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">Rp {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 text-lg">Tidak ada data kegiatan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $projectsForTable->links() }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transition-shadow duration-300" x-data="{ open: true }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-xl text-gray-800 flex items-center">
                                <i class="fas fa-money-bill-wave mr-3 text-emerald-600"></i> Alokasi & Penyerapan Anggaran per Kegiatan
                            </h3>
                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        </div>
                        <div class="space-y-5" x-show="open" x-transition>
                            @forelse($budgetByProject as $project)
                                <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors duration-150 border border-gray-100 hover:border-blue-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-2">
                                        <a href="{{ route('projects.show', $project) }}" class="text-base font-semibold text-indigo-700 hover:text-indigo-900">
                                            {{ Str::limit($project->name, 45) }}
                                        </a>
                                        <span class="text-base font-extrabold text-gray-800">{{ $project->absorption_rate }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $budget = $project->budget_items_sum_total_cost ?? 0;
                                            $absorptionRate = $project->absorption_rate;
                                            $colorClass = 'bg-green-600';
                                            if ($absorptionRate > 100 || ($budget == 0 && $project->total_realization > 0)) {
                                                $colorClass = 'bg-red-600';
                                            }
                                        @endphp
                                        <div class="{{ $colorClass }} h-3 rounded-full shadow-inner" style="width: {{ min($absorptionRate, 100) }}%"></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-2 text-xs text-gray-600 font-medium">
                                        <span>Realisasi: <span class="text-gray-900">Rp {{ number_format($project->total_realization, 0, ',', '.') }}</span></span>
                                        <span>Total Anggaran: <span class="text-gray-900">Rp {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</span></span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-8 text-lg">Tidak ada data anggaran per kegiatan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Sorotan & Kinerja SDM --}}
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transition-shadow duration-300"> {{-- Meningkatkan shadow --}}
                        <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3 text-red-600"></i> Kegiatan Prioritas / Penting
                        </h3>
                        <div class="space-y-4">
                            @forelse($criticalProjects as $project)
                                @php
                                    $healthStatus = 'Berisiko'; $healthColor = 'border-amber-500 bg-amber-50 text-amber-800';
                                    if ($project->status === 'overdue' || ($project->end_date && $project->end_date < now())) { $healthStatus = 'Kritis'; $healthColor = 'border-red-500 bg-red-50 text-red-800'; }
                                @endphp
                                <a href="{{ route('projects.show', $project) }}" class="block p-4 border-l-4 rounded-r-lg hover:bg-gray-50/50 {{ $healthColor }} transform hover:scale-[1.02] transition-transform duration-200 shadow-md hover:shadow-lg">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold text-gray-900">{{ $project->name }}</p>
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $healthColor }}">{{ $healthStatus }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1.5 flex items-center">
                                        <i class="far fa-calendar-alt mr-1"></i> Deadline: {{ $project->end_date ? $project->end_date->format('d M Y') : 'N/A' }}
                                    </p>
                                </a>
                            @empty
                                <p class="text-center text-gray-500 py-8 text-lg">Tidak ada kegiatan kritis atau berisiko.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transition-shadow duration-300"> {{-- Meningkatkan shadow --}}
                        <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-star mr-3 text-yellow-500"></i> Kinerja SDM Tertinggi
                        </h3>
                        <ul class="space-y-3">
                            @forelse($topPerformers as $performer)
                                <li class="flex items-center justify-between text-base py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 rounded px-2 -mx-2 transition-colors duration-100">
                                    <span class="flex items-center"><i class="fas fa-user-tie text-gray-400 mr-3"></i>{{ Str::limit($performer->name, 25) }}</span>
                                    <span class="font-extrabold text-green-700 px-3 py-1 bg-green-100 rounded-full shadow-sm">{{ number_format($performer->getFinalPerformanceValueAttribute(), 2) }}</span>
                                </li>
                            @empty
                                <p class="text-sm text-gray-500 py-4">Tidak ada data.</p>
                            @endforelse
                        </ul>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transition-shadow duration-300"> {{-- Meningkatkan shadow --}}
                        <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-hourglass-half mr-3 text-purple-600"></i> Utilisasi SDM Tertinggi
                        </h3>
                        <ul class="space-y-3">
                            @forelse($mostUtilized as $loaded)
                                @php
                                    $utilizationColor = 'text-green-700 bg-green-100';
                                    if ($loaded->utilization > 110) { $utilizationColor = 'text-red-700 bg-red-100'; } elseif ($loaded->utilization > 90) { $utilizationColor = 'text-amber-700 bg-amber-100'; }
                                @endphp
                                <li class="flex items-center justify-between text-base py-2 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 rounded px-2 -mx-2 transition-colors duration-100">
                                    <span class="flex items-center"><i class="fas fa-users-cog text-gray-400 mr-3"></i>{{ Str::limit($loaded->name, 25) }}</span>
                                    <span class="font-extrabold px-3 py-1 rounded-full shadow-sm {{ $utilizationColor }}">{{ $loaded->utilization }}%</span>
                                </li>
                            @empty
                                <p class="text-sm text-gray-500 py-4">Tidak ada data.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk Chart.js: Hanya definisikan data di sini --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pastikan window.performanceTrends didefinisikan untuk digunakan oleh app.js
        window.performanceTrends = {
            labels: @json($performanceTrends['labels'] ?? []),
            progress: @json($performanceTrends['progress'] ?? []),
            absorption: @json($performanceTrends['absorption'] ?? [])
        };
    </script>
    @endpush
</x-app-layout>
