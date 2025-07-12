<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ringkasan Eksekutif Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Bagian KPI Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-indigo-600">{{ $activeProjects }}</div>
                    <p class="text-sm text-gray-500 mt-1">Proyek Aktif</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-red-600">{{ $overdueProjectsCount }}</div>
                    <p class="text-sm text-gray-500 mt-1">Proyek Perlu Perhatian</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-green-600">{{ $budgetAbsorptionRate }}%</div>
                    <p class="text-sm text-gray-500 mt-1">Penyerapan Anggaran</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-blue-600">{{ $overallProgress }}%</div>
                    <p class="text-sm text-gray-500 mt-1">Progres Portofolio</p>
                </div>
            </div>

            {{-- Grafik Tren Kinerja --}}
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">Tren Kinerja Portofolio (6 Bulan Terakhir)</h3>
                <div>
                    <canvas id="performanceTrendChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Kolom Kiri: Daftar Proyek & Alokasi Anggaran --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Daftar Portofolio Proyek --}}
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Ringkasan Portofolio Program/Kegiatan</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Program/Kegiatan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progres</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggaran</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($projects as $project)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900"><a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">{{ $project->name }}</a></div><div class="text-sm text-gray-500">P.Jawab: {{ $project->leader->name ?? 'N/A' }}</div></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $project->status_color_class }}">{{ Str::title(str_replace('_', ' ', $project->status)) }}</span></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">{{ $project->progress }}%</div><div class="w-full bg-gray-200 rounded-full h-1.5"><div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $project->progress }}%"></div></div></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">Rp {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data proyek.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Alokasi & Penyerapan Anggaran per Proyek</h3>
                        <div class="space-y-4">
                            @forelse($budgetByProject as $project)
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            {{ Str::limit($project->name, 40) }}
                                        </a>
                                        <span class="text-sm font-semibold text-gray-800">{{ $project->absorption_rate }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        {{-- LOGIKA BARU UNTUK WARNA & LEBAR BAR --}}
                                        @php
                                            $budget = $project->budget_items_sum_total_cost ?? 0;
                                            $absorptionRate = $project->absorption_rate;
                                            $colorClass = 'bg-green-600'; // Default
                                            if ($absorptionRate > 100 || ($budget == 0 && $project->total_realization > 0)) {
                                                $colorClass = 'bg-red-600'; // Merah jika over atau anomali
                                            }
                                        @endphp
                                        <div class="{{ $colorClass }} h-2.5 rounded-full" style="width: {{ min($absorptionRate, 100) }}%"></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-1 text-xs text-gray-500">
                                        <span>Realisasi: Rp {{ number_format($project->total_realization, 0, ',', '.') }}</span>
                                        <span>Total Anggaran: Rp {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-4">Tidak ada data anggaran per proyek.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Sorotan & Kinerja SDM --}}
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Proyek Perlu Perhatian Strategis</h3>
                        <div class="space-y-4">
                            @forelse($criticalProjects as $project)
                                @php
                                    $healthStatus = 'Berisiko'; $healthColor = 'border-amber-400 bg-amber-50 text-amber-800';
                                    if ($project->status === 'overdue' || ($project->end_date && $project->end_date < now())) { $healthStatus = 'Kritis'; $healthColor = 'border-red-400 bg-red-50 text-red-800'; }
                                @endphp
                                <a href="{{ route('projects.show', $project) }}" class="block p-4 border-l-4 rounded-r-lg hover:bg-gray-50/50 {{ $healthColor }}">
                                    <div class="flex justify-between items-center"><p class="font-bold text-gray-900">{{ $project->name }}</p><span class="text-xs font-semibold px-2 py-1 rounded-full {{ $healthColor }}">{{ $healthStatus }}</span></div>
                                    <p class="text-xs text-gray-600 mt-1">Deadline: {{ $project->end_date ? $project->end_date->format('d M Y') : 'N/A' }}</p>
                                </a>
                            @empty
                                <p class="text-center text-gray-500 py-8">Tidak ada proyek kritis atau berisiko.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-lg text-gray-800 mb-4">Kinerja SDM Tertinggi</h3><ul class="space-y-3">@forelse($topPerformers as $performer)<li class="flex items-center justify-between text-sm"><span>{{ Str::limit($performer->name, 25) }}</span><span class="font-bold text-green-700 px-2 py-1 bg-green-100 rounded-md">{{ number_format($performer->getFinalPerformanceValueAttribute(), 2) }}</span></li>@empty<p class="text-sm text-gray-500">Tidak ada data.</p>@endforelse</ul></div>
                    <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-lg text-gray-800 mb-4">Utilisasi SDM Tertinggi</h3><ul class="space-y-3">@forelse($mostUtilized as $loaded)@php $utilizationColor = 'text-green-700 bg-green-100'; if ($loaded->utilization > 110) { $utilizationColor = 'text-red-700 bg-red-100'; } elseif ($loaded->utilization > 90) { $utilizationColor = 'text-amber-700 bg-amber-100'; } @endphp<li class="flex items-center justify-between text-sm"><span>{{ Str::limit($loaded->name, 25) }}</span><span class="font-semibold px-2 py-1 rounded-md {{ $utilizationColor }}">{{ $loaded->utilization }}%</span></li>@empty<p class="text-sm text-gray-500">Tidak ada data.</p>@endforelse</ul></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk Chart.js (tidak ada perubahan) --}}
    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('performanceTrendChart');
                const trendData = {
                    labels: @json($performanceTrends['labels']),
                    progress: @json($performanceTrends['progress']),
                    absorption: @json($performanceTrends['absorption'])
                };

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels,
                        datasets: [{
                            label: 'Progres Portofolio (%)',
                            data: trendData.progress,
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.3
                        }, {
                            label: 'Penyerapan Anggaran (%)',
                            data: trendData.absorption,
                            borderColor: 'rgb(22, 163, 74)',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true, max: 100, ticks: { callback: (value) => value + '%' }}},
                        plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false }},
                        interaction: { mode: 'nearest', axis: 'x', intersect: false }
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>