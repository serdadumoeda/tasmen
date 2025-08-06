<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-briefcase mr-2"></i> {{ __('Daftar Kegiatan') }}
            </h2>
            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create.step1') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Kegiatan
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Grid Metrik Utama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Kegiatan -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-blue-500 p-4 rounded-full">
                        <i class="fas fa-folder-open fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_projects'] }}</p>
                        <p class="text-gray-500 font-medium">Total Kegiatan</p>
                    </div>
                </div>
                @if (!auth()->user()->isStaff())
                <!-- Pengguna Aktif -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-green-500 p-4 rounded-full">
                        <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['active_users'] }}/<span class="text-2xl text-gray-600">{{ $stats['total_users'] }}</span></p>
                        <p class="text-gray-500 font-medium">Pengguna Aktif</p>
                    </div>
                </div>
                @endif
                <!-- Total Tugas -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-orange-500 p-4 rounded-full">
                        <i class="fas fa-tasks fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['completed_tasks'] }}/<span class="text-2xl text-gray-600">{{ $stats['total_tasks'] }}</span></p>
                        <p class="text-gray-500 font-medium">Tugas Selesai</p>
                    </div>
                </div>
                @if (!auth()->user()->isStaff())
                <!-- Permintaan Tertunda -->
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4">
                    <div class="bg-yellow-500 p-4 rounded-full">
                        <i class="fas fa-inbox fa-2x text-white"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_requests'] }}</p>
                        <p class="text-gray-500 font-medium">Permintaan Tertunda</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Grid Konten Utama (Daftar Kegiatan dan Aktivitas) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Kolom Kiri: Daftar Kegiatan -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Form Filter dan Pencarian -->
                    <div class="bg-white p-4 rounded-xl shadow-lg">
                        <form action="{{ route('global.dashboard') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <div class="md:col-span-2">
                                <label for="search" class="sr-only">Cari Kegiatan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" name="search" id="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Cari nama kegiatan..." value="{{ $search ?? '' }}">
                                </div>
                            </div>
                            <div>
                                <label for="status" class="sr-only">Status</label>
                                <select id="status" name="status" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Semua Status</option>
                                    <option value="pending" @selected(($status ?? '') === 'pending')>Menunggu</option>
                                    <option value="in_progress" @selected(($status ?? '') === 'in_progress')>Dikerjakan</option>
                                    <option value="completed" @selected(($status ?? '') === 'completed')>Selesai</option>
                                    <option value="overdue" @selected(($status ?? '') === 'overdue')>Terlambat</option>
                                </select>
                            </div>
                            <div class="md:col-span-3 flex justify-end space-x-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Filter</button>
                                <a href="{{ route('global.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Reset</a>
                            </div>
                        </form>
                    </div>

                    <!-- Daftar Proyek -->
                    @forelse ($allProjects as $project)
                        @php
                            // Kalkulasi ini bisa dipindahkan ke model jika sering digunakan
                            $totalTasks = $project->tasks->count();
                            $completedTasks = $project->tasks->where('status', 'completed')->count();
                            $completionPercentage = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;
                            // Menggunakan status dinamis dari model Project
                            $statusInfo = $project->status;
                            $statusClass = $project->getStatusColorClassAttribute();
                        @endphp
                        <a href="{{ route('projects.show', $project) }}" class="block bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-lg text-gray-800">{{ $project->name }}</h4>
                                <span class="text-xs font-semibold {{ $statusClass }} px-3 py-1 rounded-full">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $statusInfo)) }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mb-1">
                                <i class="fas fa-user-tie mr-2 text-gray-400"></i>Ketua: <span class="font-medium text-gray-700">{{ $project->leader->name }}</span>
                            </p>
                             <p class="text-sm text-gray-500 mb-3">
                                <i class="fas fa-crown mr-2 text-gray-400"></i>Pemilik: <span class="font-medium text-gray-700">{{ $project->owner->name }}</span>
                            </p>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Progress: {{ $completionPercentage }}%</span>
                                <span>{{ $completedTasks }} / {{ $totalTasks }} Tugas Selesai</span>
                            </div>
                        </a>
                    @empty
                        <div class="bg-white p-10 rounded-xl shadow-lg text-center">
                            <i class="fas fa-search-minus fa-3x text-gray-300 mb-4"></i>
                            <p class="text-gray-500 font-semibold">Tidak ada kegiatan yang cocok dengan kriteria Anda.</p>
                            <p class="text-sm text-gray-400 mt-2">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </div>
                    @endforelse

                    <!-- Navigasi Paginasi -->
                    <div class="mt-8">
                        {{ $allProjects->links() }}
                    </div>
                </div>

                <!-- Kolom Kanan: Aktivitas Terbaru -->
                <div class="bg-white overflow-hidden shadow-xl rounded-xl p-6">
                     <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
                        <i class="fas fa-history mr-3 text-indigo-500"></i>
                        Aktivitas Terbaru Sistem
                    </h3>
                     <ul class="space-y-4">
                        @forelse($recentActivities as $activity)
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0 pt-1">
                                    @switch($activity->description)
                                        @case('created_project') <i class="fas fa-folder-plus text-blue-500"></i> @break
                                        @case('created_task') <i class="fas fa-check-circle text-green-500"></i> @break
                                        @case('updated_task') <i class="fas fa-edit text-yellow-500"></i> @break
                                        @case('created_user') <i class="fas fa-user-plus text-purple-500"></i> @break
                                        @default <i class="fas fa-dot-circle text-gray-400"></i> @break
                                    @endswitch
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-bold text-gray-800">{{ optional($activity->user)->name ?? 'Sistem' }}</span>
                                        @switch($activity->description)
                                            @case('created_project') membuat proyek baru @break
                                            @case('created_task') membuat tugas baru @break
                                            @case('updated_task') memperbarui sebuah tugas @break
                                            @case('created_user') mendaftarkan pengguna baru @break
                                            @default melakukan sebuah aktivitas @break
                                        @endswitch
                                        <span class="font-semibold text-indigo-600">{{ optional($activity->subject)->name ?? optional($activity->subject)->title ?? '' }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-gray-500 py-8">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p>Belum ada aktivitas tercatat.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>