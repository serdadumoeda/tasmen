<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Beranda') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Salam Pembuka -->
            <div class="mb-8 p-6 bg-white border border-gray-200 rounded-2xl shadow-lg">
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ $user->name }}!</h1>
                <p class="text-gray-600 mt-1">Berikut adalah ringkasan cepat dari pekerjaan Anda.</p>
            </div>

            <!-- Grid Utama -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Kolom Kiri (Utama) -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Widget Statistik Cepat -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4">
                            <div class="bg-blue-100 p-3 rounded-full"><i class="fas fa-spinner text-blue-600 fa-lg"></i></div>
                            <div>
                                <p class="text-3xl font-extrabold text-gray-800">{{ $tasksInProgress }}</p>
                                <p class="text-sm text-gray-500 font-medium">Tugas Dikerjakan</p>
                            </div>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4">
                            <div class="bg-yellow-100 p-3 rounded-full"><i class="fas fa-exclamation-circle text-yellow-600 fa-lg"></i></div>
                            <div>
                                <p class="text-3xl font-extrabold text-gray-800">{{ $dueSoonTasks->count() }}</p>
                                <p class="text-sm text-gray-500 font-medium">Mendekati Deadline</p>
                            </div>
                        </div>
                        <div class="bg-white p-5 rounded-xl shadow-md flex items-center space-x-4">
                            <div class="bg-red-100 p-3 rounded-full"><i class="fas fa-fire-alt text-red-600 fa-lg"></i></div>
                            <div>
                                <p class="text-3xl font-extrabold text-gray-800">{{ $overdueTasks->count() }}</p>
                                <p class="text-sm text-gray-500 font-medium">Lewat Deadline</p>
                            </div>
                        </div>
                    </div>

                    <!-- Widget Perlu Perhatian Segera -->
                    <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-lg">
                        <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center"><i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>Perlu Perhatian Segera</h3>
                        <div class="space-y-4">
                            @if($overdueTasks->isEmpty() && $dueSoonTasks->isEmpty())
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-check-circle text-green-500 fa-3x mb-3"></i>
                                    <p class="font-medium">Kerja bagus! Tidak ada tugas yang mendesak.</p>
                                </div>
                            @endif

                            @foreach($overdueTasks->take(3) as $task)
                                <div class="p-4 rounded-lg bg-red-50 border border-red-200 flex justify-between items-center">
                                    <div>
                                        <a href="{{ $task->project ? route('projects.show', $task->project_id) : route('adhoc-tasks.index') }}" class="font-semibold text-red-800 hover:underline">{{ $task->title }}</a>
                                        <p class="text-xs text-red-600">Terlambat {{ now()->diffInDays($task->deadline) }} hari</p>
                                    </div>
                                    <a href="{{ $task->project ? route('projects.show', $task->project_id) : route('adhoc-tasks.edit', $task) }}" class="text-xs font-bold text-white bg-red-500 px-3 py-1 rounded-full hover:bg-red-600 transition-colors">Lihat</a>
                                </div>
                            @endforeach

                            @foreach($dueSoonTasks->take(3) as $task)
                                <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200 flex justify-between items-center">
                                    <div>
                                        <a href="{{ $task->project ? route('projects.show', $task->project_id) : route('adhoc-tasks.index') }}" class="font-semibold text-yellow-800 hover:underline">{{ $task->title }}</a>
                                        <p class="text-xs text-yellow-600">Sisa waktu {{ now()->diffInDays($task->deadline) + 1 }} hari</p>
                                    </div>
                                    <a href="{{ $task->project ? route('projects.show', $task->project_id) : route('adhoc-tasks.edit', $task) }}" class="text-xs font-bold text-white bg-yellow-500 px-3 py-1 rounded-full hover:bg-yellow-600 transition-colors">Lihat</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan (Samping) -->
                <div class="space-y-8">
                    <!-- Widget Persetujuan Menunggu -->
                    @if($user->canManageUsers() && $pendingApprovals->isNotEmpty())
                    <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-lg">
                        <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center"><i class="fas fa-check-double text-blue-500 mr-3"></i>Persetujuan Menunggu</h3>
                        <ul class="space-y-3">
                            @foreach($pendingApprovals->take(5) as $item)
                                <li class="text-sm">
                                    @if($item instanceof \App\Models\Task)
                                        <a href="{{ route('projects.show', $item->project_id) }}" class="text-indigo-600 hover:underline">
                                            <i class="fas fa-tasks mr-2 text-gray-400"></i>Persetujuan tugas '{{ Str::limit($item->title, 25) }}'
                                        </a>
                                    @elseif($item instanceof \App\Models\PeminjamanRequest)
                                        <a href="{{ route('peminjaman-requests.my-requests') }}" class="text-indigo-600 hover:underline">
                                            <i class="fas fa-user-plus mr-2 text-gray-400"></i>Pinjam '{{ $item->requestedUser->name }}'
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Widget Aktivitas Terbaru Saya -->
                    <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-lg">
                        <h3 class="font-bold text-xl text-gray-800 mb-4 flex items-center"><i class="fas fa-history text-purple-500 mr-3"></i>Aktivitas Terbaru Saya</h3>
                        <ul class="space-y-4">
                            @forelse($myActivities as $activity)
                                <li class="flex items-start space-x-3">
                                    <div class="bg-purple-100 p-2 rounded-full mt-1"><i class="fas fa-stream text-purple-600"></i></div>
                                    <div>
                                        <p class="text-sm text-gray-700">{!! $activity->description !!}</p>
                                        <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-4 text-gray-500">
                                    <p>Belum ada aktivitas.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
