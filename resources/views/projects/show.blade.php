<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Proyek: {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .progress-bar { transition: width 0.6s ease; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 md:p-8">

        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">&larr; Kembali ke Dashboard</a>
            <div class="flex items-start md:items-center mt-2 flex-col md:flex-row">
                <h1 class="text-4xl font-bold text-gray-800">{{ $project->name }}</h1>
                <div class="ms-auto flex items-center space-x-3 mt-2 md:mt-0">
                    
                    {{-- PERBAIKAN: Tombol aksi untuk level Proyek --}}
                    @can('update', $project)
                        <a href="{{ route('projects.edit', $project) }}" class="inline-block bg-yellow-500 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-yellow-600 transition-colors">
                            Edit Proyek
                        </a>
                    @endcan
                    @can('delete', $project)
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini? Semua tugas di dalamnya akan ikut terhapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-block bg-red-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-red-700 transition-colors">
                                Hapus Proyek
                            </button>
                        </form>
                    @endcan

                    @can('viewTeamDashboard', $project)
                        <a href="{{ route('projects.team.dashboard', $project) }}" class="inline-block bg-blue-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                            Lihat Dashboard Tim
                        </a>
                    @endcan
                    @if(in_array(Auth::user()->role, ['superadmin', 'Eselon I', 'Eselon II']))
                        <a href="{{ route('projects.report', $project) }}" target="_blank" class="inline-block bg-gray-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-gray-700 transition-colors">
                            Download Laporan PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Ringkasan Proyek</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                    <p class="text-gray-500">Total Tugas</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-3xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
                    <p class="text-gray-500">Tugas Pending</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-3xl font-bold text-orange-500">{{ $stats['in_progress'] }}</p>
                    <p class="text-gray-500">Sedang Dikerjakan</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-3xl font-bold text-green-500">{{ $stats['completed'] }}</p>
                    <p class="text-gray-500">Tugas Selesai</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                {{-- PERBAIKAN: Form tambah tugas hanya untuk pimpinan proyek --}}
                @if(Auth::id() === $project->leader_id || Auth::id() === $project->owner_id)
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-4 border-b border-gray-200 pb-2 text-gray-800">Tambah Tugas Baru</h3>
                    <form action="{{ route('tasks.store', $project) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Judul Tugas</label>
                                <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label for="deadline" class="block text-sm font-medium text-gray-700">Deadline</label>
                                <input type="date" name="deadline" id="deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div class="md:col-span-2">
                                <label for="assigned_to_id" class="block text-sm font-medium text-gray-700">Tugaskan Kepada</label>
                                <select name="assigned_to_id" id="assigned_to_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Pilih Anggota --</option>
                                    @foreach($projectMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Tambah Tugas</button>
                    </form>
                </div>
                @endif

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-4 border-b border-gray-200 pb-2 text-gray-800">Daftar Tugas</h3>
                    <div class="space-y-4">
                        @forelse($project->tasks as $task)
                            @php
                                $isOverdue = $task->deadline < now() && $task->status != 'completed';
                            @endphp
                            <div class="border border-gray-200 p-4 rounded-lg @if($isOverdue) border-red-300 bg-red-50 @endif">
                                
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-lg text-gray-800">{{ $task->title }}</h4>
                                        <p class="text-sm text-gray-600">Untuk: <strong>{{ $task->assignedTo->name }}</strong> | Deadline:
                                            <span class="@if($isOverdue) text-red-700 font-bold @endif">
                                                {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}
                                            </span>
                                        </p>
                                    </div>
                                    
                                    {{-- PERBAIKAN: Tombol aksi tugas hanya untuk yang berwenang --}}
                                    @can('update', $task)
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <a href="{{ route('tasks.edit', $task) }}" class="inline-block px-3 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full hover:bg-amber-200 transition-colors">
                                            Edit
                                        </a>
                                        @can('delete', $task)
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-block px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full hover:bg-red-200 transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                    @endcan
                                </div>

                                <div class="mt-2">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-base font-medium text-blue-700">Progress</span>
                                        <span class="text-sm font-medium text-blue-700">{{ $task->progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                </div>
                                
                                {{-- PERBAIKAN: Izin untuk mengelola rincian, lampiran, dan waktu kerja --}}
                                @can('update', $task)
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <h5 class="font-semibold text-sm mb-2 text-gray-700">Rincian Tugas</h5>
                                    <div class="space-y-2">
                                        @forelse($task->subTasks as $subTask)
                                            <div class="flex items-center justify-between">
                                                <form action="{{ route('subtasks.update', $subTask) }}" method="POST" class="flex items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="checkbox" name="is_completed" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                                        onchange="this.form.submit()"
                                                        @if($subTask->is_completed) checked @endif>
                                                    <label class="ml-3 text-sm {{ $subTask->is_completed ? 'line-through text-gray-500' : 'text-gray-800' }}">
                                                        {{ $subTask->title }}
                                                    </label>
                                                </form>
                                                <form action="{{ route('subtasks.destroy', $subTask) }}" method="POST" onsubmit="return confirm('Hapus rincian tugas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600">&times;</button>
                                                </form>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-500">Belum ada rincian tugas.</p>
                                        @endforelse
                                    </div>
                                    <form action="{{ route('subtasks.store', $task) }}" method="POST" class="mt-3 flex space-x-2">
                                        @csrf
                                        <input type="text" name="title" class="flex-grow block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tambah rincian baru..." required>
                                        <button type="submit" class="px-3 py-1 bg-gray-700 text-white text-xs font-bold rounded hover:bg-gray-800">Tambah</button>
                                    </form>
                                </div>
                                
                                <div class="mt-4 border-t border-gray-200 pt-4"
                                     x-data="{
                                        showManualForm: false,
                                        runningTaskForThisUser: {{ Auth::user()->timeLogs()->whereNull('end_time')->first()->task_id ?? 'null' }}
                                     }"
                                     x-cloak
                                >
                                    <h5 class="font-semibold text-sm mb-2 text-gray-700">Pencatatan Waktu</h5>
                                    <div class="flex justify-between items-center text-sm">
                                        <div>
                                            @php
                                                $totalMinutes = $task->timeLogs->sum('duration_in_minutes');
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            <p>Waktu Estimasi: <span class="font-bold">{{ (float)$task->estimated_hours ?? 0 }} jam</span></p>
                                            <p>Waktu Tercatat: <span class="font-bold text-blue-600">{{ $hours }} jam {{ $minutes }} menit</span></p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <template x-if="runningTaskForThisUser !== {{ $task->id }}">
                                                <button @click="startTimer({{ $task->id }})" class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded hover:bg-green-600" :disabled="runningTaskForThisUser !== null">
                                                    START
                                                </button>
                                            </template>
                                            <template x-if="runningTaskForThisUser === {{ $task->id }}">
                                                 <button @click="stopTimer({{ $task->id }})" class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded hover:bg-red-600 animate-pulse">
                                                    STOP
                                                </button>
                                            </template>
                                            <button @click="showManualForm = !showManualForm" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded hover:bg-gray-300">MANUAL</button>
                                        </div>
                                    </div>
                                    <div x-show="showManualForm" x-transition class="mt-4 border-t border-gray-200 pt-4">
                                        <p class="text-xs text-gray-600 mb-2">Catat waktu yang sudah dikerjakan (misal: kemarin).</p>
                                        <form action="{{ route('timelogs.storeManual', $task) }}" method="POST" class="flex items-end space-x-2">
                                            @csrf
                                            <div>
                                                <label for="duration_in_minutes_{{ $task->id }}" class="block text-xs text-gray-600">Menit</label>
                                                <input type="number" id="duration_in_minutes_{{ $task->id }}" name="duration_in_minutes" class="text-sm rounded-md border-gray-300 shadow-sm" style="width: 80px;" required>
                                            </div>
                                            <div>
                                                 <label for="log_date_{{ $task->id }}" class="block text-xs text-gray-600">Tanggal</label>
                                                <input type="date" id="log_date_{{ $task->id }}" name="log_date" value="{{ now()->format('Y-m-d') }}" class="text-sm rounded-md border-gray-300 shadow-sm" required>
                                            </div>
                                            <button type="submit" class="h-9 px-3 bg-blue-600 text-white text-xs font-bold rounded hover:bg-blue-700">Simpan</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <h5 class="font-semibold text-sm mb-2 text-gray-700">Lampiran</h5>
                                    <ul class="list-disc list-inside space-y-1 mb-3">
                                        @forelse($task->attachments as $attachment)
                                            <li class="text-sm flex justify-between items-center">
                                                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $attachment->filename }}</a>
                                                <form action="{{ route('attachments.destroy', $attachment) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">&times;</button>
                                                </form>
                                            </li>
                                        @empty
                                            <li class="text-sm text-gray-500 list-none">Belum ada lampiran.</li>
                                        @endforelse
                                    </ul>
                                    <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex items-center space-x-2">
                                            <input type="file" name="file" class="text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">Unggah</button>
                                        </div>
                                    </form>
                                </div>
                                @endcan

                                {{-- Diskusi bisa dilakukan oleh semua anggota tim --}}
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <h5 class="font-semibold text-sm mb-2 text-gray-700">Diskusi</h5>
                                    <div class="space-y-3 mb-4 max-h-60 overflow-y-auto">
                                        @forelse($task->comments as $comment)
                                        <div class="flex items-start space-x-2 text-sm">
                                            <span class="font-bold text-gray-800 flex-shrink-0">{{ $comment->user->name }}:</span>
                                            <p class="text-gray-700">{{ $comment->body }}</p>
                                        </div>
                                        @empty
                                        <p class="text-sm text-gray-500">Belum ada komentar.</p>
                                        @endforelse
                                    </div>
                                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST">
                                        @csrf
                                        <div class="flex space-x-2">
                                            <input type="text" name="body" class="flex-grow block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tulis komentar..." required>
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">Kirim</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Belum ada tugas di proyek ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Distribusi Status Tugas</h3>
                    <canvas id="taskStatusChart"></canvas>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Detail Proyek</h3>
                    <div class="text-gray-700 space-y-2">
                        <p>{{ $project->description }}</p>
                        <p><span class="font-semibold">Pemilik Proyek:</span> {{ $project->owner->name }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Tim Proyek</h3>
                    <ul>
                        <li class="flex items-center space-x-2">
                            <span class="font-bold text-gray-700">Pimpinan Proyek:</span>
                            <span>{{ $project->leader->name }}</span>
                        </li>
                    </ul>
                    <h4 class="font-semibold mt-4 text-gray-800">Anggota:</h4>
                    <ul class="list-disc list-inside mt-2 text-gray-700">
                        @foreach($project->members as $member)
                            <li>{{ $member->name }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Beban Tugas Tim</h3>
                    <ul class="space-y-2">
                        @foreach($project->members as $member)
                            @php
                                $taskCount = $tasksByUser->get($member->id, collect())->count();
                            @endphp
                            <li class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">{{ $member->name }}</span>
                                <span class="font-bold px-2 py-1 text-xs rounded {{ $taskCount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $taskCount }} Tugas
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Aktivitas Terbaru</h3>
                    <ul class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($project->activities->take(15) as $activity)
                            <li class="text-sm text-gray-600 border-b border-gray-200 pb-2">
                                <span class="font-semibold text-gray-800">{{ $activity->user->name }}</span>
                                @switch($activity->description)
                                    @case('created_project')
                                        membuat proyek ini
                                        @break
                                    @case('updated_project')
                                        memperbarui proyek ini
                                        @break
                                    @case('created_task')
                                        membuat tugas "{{ optional($activity->subject)->title ?? 'Tugas yang telah dihapus' }}"
                                        @break
                                    @case('updated_task')
                                        memperbarui tugas "{{ optional($activity->subject)->title ?? 'Tugas yang telah dihapus' }}"
                                        @break
                                    @case('deleted_task')
                                        menghapus sebuah tugas
                                        @break
                                    @default
                                        melakukan sebuah aktivitas
                                @endswitch
                                <span class="block text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('taskStatusChart');
            if (ctx) {
                const stats = @json($stats);
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Pending', 'Dikerjakan', 'Selesai'],
                        datasets: [{
                            label: 'Jumlah Tugas',
                            data: [stats.pending, stats.in_progress, stats.completed],
                            backgroundColor: ['#facc15', '#f97316', '#22c55e'],
                            hoverOffset: 4,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' }, title: { display: false } }
                    }
                });
            }
        });

        function startTimer(taskId) {
            fetch(`/tasks/${taskId}/time-log/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if(data.message) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function stopTimer(taskId) {
            fetch(`/tasks/${taskId}/time-log/stop`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                 if(data.message) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>