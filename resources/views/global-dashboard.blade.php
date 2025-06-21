<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Global Dashboard Pengawasan') }}
            </h2>

            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Buat Proyek Baru
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['total_projects'] }}</p>
                        <p class="text-gray-500">Total Proyek</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-gray-600">{{ $stats['total_users'] }}</p>
                        <p class="text-gray-500">Total Pengguna</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-orange-500">{{ $stats['total_tasks'] }}</p>
                        <p class="text-gray-500">Total Tugas</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow text-center">
                        <p class="text-3xl font-bold text-green-500">{{ $stats['completed_tasks'] }}</p>
                        <p class="text-gray-500">Tugas Selesai</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900">Semua Proyek</h3>
                    <div class="space-y-4">
                        @forelse ($allProjects as $project)
                            @php
                                $totalTasks = $project->tasks->count();
                                $completedTasks = $project->tasks->where('status', 'completed')->count();
                                $completionPercentage = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;

                                $statusText = 'Belum Ada Tugas';
                                $statusClass = 'bg-gray-100 text-gray-800';

                                if ($totalTasks > 0) {
                                    if ($completionPercentage == 100) {
                                        $statusText = 'Selesai';
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif ($completionPercentage > 0) {
                                        $statusText = 'Sedang Berjalan';
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                    } else {
                                        $statusText = 'Baru';
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    }
                                }
                            @endphp

                            <a href="{{ route('projects.show', $project) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-blue-600">{{ $project->name }}</p>
                                        <p class="text-sm text-gray-600">Ketua: {{ $project->leader->name }}</p>
                                        
                                        {{-- PENAMBAHAN KODE UNTUK ANGGARAN --}}
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span class="font-semibold">Anggaran:</span> Rp {{ number_format($project->budget_items_sum_total_cost ?? 0, 0, ',', '.') }}
                                        </p>
                                        {{-- AKHIR PENAMBAHAN --}}

                                    </div>
                                    <div class="text-right flex-shrink-0 ml-4">
                                        <p class="font-semibold text-gray-700">{{ $completedTasks }} / {{ $totalTasks }} Tugas</p>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="p-6 text-center text-gray-500">Tidak ada proyek untuk ditampilkan.</p>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                     <h3 class="text-lg font-medium mb-4 text-gray-900">Aktivitas Terbaru Sistem</h3>
                     <ul class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <li class="text-sm text-gray-600 border-b border-gray-200 pb-2">
                                <span class="font-semibold text-gray-800">{{ optional($activity->user)->name ?? 'User tidak dikenal' }}</span>
                                @switch($activity->description)
                                    @case('created_project') membuat proyek "{{ optional($activity->subject)->name ?? '' }}" @break
                                    @case('created_task') membuat tugas "{{ optional($activity->subject)->title ?? '...' }}" @break
                                    @case('updated_task') memperbarui tugas "{{ optional($activity->subject)->title ?? '...' }}" @break
                                    @case('deleted_task') menghapus sebuah tugas @break
                                    @case('created_user') membuat user baru: {{ optional($activity->subject)->name ?? '' }} @break
                                    @case('updated_user') memperbarui data user: {{ optional($activity->subject)->name ?? '' }} @break
                                    @case('deleted_user') menghapus user @break
                                    @default melakukan sebuah aktivitas @break
                                @endswitch
                                <span class="block text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>