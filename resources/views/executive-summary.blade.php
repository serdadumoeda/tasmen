<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Executive Summary Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-indigo-600">{{ $activeProjects }}</div>
                    <p class="text-sm text-gray-500 mt-1">Proyek Aktif</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-red-600">{{ $overdueProjects }}</div>
                    <p class="text-sm text-gray-500 mt-1">Proyek Kritis/Terlambat</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-green-600">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
                    <p class="text-sm text-gray-500 mt-1">Total Anggaran Portofolio</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="text-4xl font-bold text-blue-600">{{ $overallProgress }}%</div>
                    <p class="text-sm text-gray-500 mt-1">Progres Keseluruhan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Proyek Perlu Perhatian</h3>
                    <div class="space-y-4">
                        @forelse($criticalProjects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block p-4 border rounded-lg hover:bg-gray-50/50 @if($project->end_date < now()) border-red-300 bg-red-50 @else border-amber-300 bg-amber-50 @endif">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $project->name }}</p>
                                        <p class="text-sm text-gray-600">Ketua: {{ $project->leader->name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold @if($project->end_date < now()) text-red-600 @else text-amber-600 @endif">
                                            Deadline: {{ $project->end_date->format('d M Y') }}
                                        </p>
                                        <span class="text-xs text-gray-500">{{ $project->end_date->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-gray-500 py-8">Tidak ada proyek yang kritis saat ini. Kerja bagus!</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Performa Tertinggi</h3>
                        <ul class="space-y-3">
                            @forelse($topPerformers as $performer)
                            <li class="flex items-center justify-between text-sm">
                                <span>{{ $performer->name }}</span>
                                <span class="font-bold text-green-700 px-2 py-1 bg-green-100 rounded-md">
                                   {{ number_format($performer->getFinalPerformanceValueAttribute(), 2) }}
                                </span>
                            </li>
                            @empty
                            <p class="text-sm text-gray-500">Tidak ada data performa.</p>
                            @endforelse
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">Beban Kerja Tertinggi</h3>
                        <ul class="space-y-3">
                            @forelse($mostLoaded as $loaded)
                            <li class="flex items-center justify-between text-sm">
                                <span>{{ $loaded->name }}</span>
                                <span class="font-semibold text-red-700 px-2 py-1 bg-red-100 rounded-md">
                                    {{ $loaded->total_project_hours }} Jam
                                </span>
                            </li>
                            @empty
                             <p class="text-sm text-gray-500">Tidak ada data beban kerja.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Portofolio Proyek</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Proyek
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Progres
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Anggaran
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($projects as $project)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $project->name }}
                                                </a>
                                            </div>
                                            <div class="text-sm text-gray-500">Ketua: {{ $project->leader->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $project->status_color_class }}">
                                            {{ Str::title(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $project->progress }}%</div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $project->progress }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                            Rp {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada proyek yang ditemukan.
                                        </td>
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