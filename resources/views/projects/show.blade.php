<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Proyek: {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Memuat Tom Select CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.css" rel="stylesheet">
    <style>
        .progress-bar { transition: width 0.6s ease; }
    </style>
</head>
<body class="bg-gray-100"
    x-data="{
        runningTaskGlobal: {{ optional(Auth::user()->timeLogs()->whereNull('end_time')->first())->task_id ?? 'null' }},

        async postData(url) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Terjadi kesalahan pada server.');
            }
            return response.json();
        },

        async startTimer(taskId) {
            try {
                const data = await this.postData(`/tasks/${taskId}/time-log/start`);
                console.log(data.message);
                this.runningTaskGlobal = taskId;
                location.reload(); // Refresh halaman untuk update tampilan timer
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memulai timer: ' + error.message);
            }
        },

        async stopTimer(taskId) {
            try {
                await this.postData(`/tasks/${taskId}/time-log/stop`);
                this.runningTaskGlobal = null;
                location.reload(); // Refresh halaman untuk update waktu tercatat
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menghentikan timer: ' + error.message);
            }
        }
    }"
>
    <div class="container mx-auto p-4 md:p-8">

        <div class="mb-6">
            <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800 font-medium">&larr; Kembali</a>
            <div class="flex flex-wrap items-center mt-2">
                <h1 class="text-4xl font-bold text-gray-800 mr-4">{{ $project->name }}</h1>
                <div class="flex items-center space-x-2 flex-wrap mt-2 sm:mt-0">
                    {{-- ========================================================== --}}
                    {{-- PENAMBAHAN 1: Tombol Tampilan Kanban & Kalender            --}}
                    {{-- ========================================================== --}}
                    <a href="{{ route('projects.kanban', $project) }}" class="inline-block bg-purple-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-purple-700 transition-colors">
                        Papan Kanban
                    </a>
                    <a href="{{ route('projects.calendar', $project) }}" class="inline-block bg-sky-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-sky-700 transition-colors">
                        Kalender
                    </a>
                    @can('viewTeamDashboard', $project)
                        <a href="{{ route('projects.team.dashboard', $project) }}" class="inline-block bg-blue-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                            Dashboard Tim
                        </a>
                    @endcan
                    @if(in_array(optional(Auth::user())->role, ['superadmin', 'Eselon I', 'Eselon II']))
                        <a href="{{ route('projects.report', $project) }}" target="_blank" class="inline-block bg-gray-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-gray-700 transition-colors">
                            Laporan PDF
                        </a>
                    @endif
                    @can('update', $project)
                        <a href="{{ route('projects.edit', $project) }}" class="inline-block bg-amber-500 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-amber-600 transition-colors">
                            Edit Proyek
                        </a>
                    @endcan
                    @can('update', $project)
                        <a href="{{ route('projects.budget-items.index', $project) }}" class="inline-block bg-green-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-green-700 transition-colors">
                            Anggaran
                        </a>
                    @endcan
                    @if($project->start_date && $project->end_date)
                        <a href="{{ route('projects.s-curve', $project) }}" class="inline-block bg-teal-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-teal-700 transition-colors">
                            Kurva S
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
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg" role="alert">
                <p class="font-bold">Oops! Terjadi kesalahan:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ... (Bagian Ringkasan Proyek tetap sama) ... --}}
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

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-4 border-b border-gray-200 pb-2 text-gray-800">Tambah Tugas Baru</h3>
                    <form action="{{ route('tasks.store', $project) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Judul Tugas</label>
                                <input type="text" name="name" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name') }}" required>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="deadline" class="block text-sm font-medium text-gray-700">Deadline</label>
                                    <input type="date" name="deadline" id="deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('deadline') }}">
                                </div>
                                <div>
                                    <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Estimasi Jam</label>
                                    <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('estimated_hours') }}">
                                </div>
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700">Prioritas</label>
                                    <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="low">Rendah</option>
                                        <option value="medium" selected>Sedang</option>
                                        <option value="high">Tinggi</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="assignees" class="block text-sm font-medium text-gray-700">Tugaskan Kepada</label>
                                <select name="assignees[]" id="assignees" multiple>
                                    @foreach($projectMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Tambah Tugas</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-4 border-b border-gray-200 pb-2 text-gray-800">Daftar Tugas</h3>
                    <div class="space-y-4">
                        @forelse($project->tasks()->orderBy('deadline', 'asc')->get() as $task)
                            @php
                                $isOverdue = $task->deadline && $task->deadline < now() && $task->progress < 100;
                            @endphp
                            <div class="border border-gray-200 p-4 rounded-lg @if($isOverdue) border-red-300 bg-red-50 @endif" id="task-{{ $task->id }}">
                                
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-lg text-gray-800">{{ $task->name }}</h4>
                                        <p class="text-sm text-gray-600">Untuk: 
                                            <strong>
                                                @foreach($task->assignees as $assignee)
                                                    {{ $assignee->name }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </strong> | Deadline:
                                            <span class="@if($isOverdue) text-red-700 font-bold @endif">
                                                {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : 'N/A' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        @can('update', $task)
                                            <a href="{{ route('tasks.edit', $task) }}" class="inline-block px-3 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full hover:bg-amber-200 transition-colors">
                                                Edit
                                            </a>
                                        @endcan
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
                                </div>

                                <div class="mt-2">
                                    <div class="flex justify-between mb-1 items-center">
                                        <span class="text-base font-medium text-blue-700">Progress</span>
                                        <div>
                                            {{-- ========================================================== --}}
                                            {{-- PENAMBAHAN 2: Tampilan Status "Menunggu Review"             --}}
                                            {{-- ========================================================== --}}
                                            @if($task->pending_review)
                                                <span class="px-2 py-1 text-xs font-semibold text-orange-800 bg-orange-200 rounded-full">
                                                    Menunggu Review
                                                </span>
                                            @endif
                                            <span class="text-sm font-medium text-blue-700 ml-2">{{ $task->progress }}%</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                </div>
                                
                                {{-- ========================================================== --}}
                                {{-- PENAMBAHAN 3: Tombol Aksi "Setujui"                       --}}
                                {{-- ========================================================== --}}
                                <div class="mt-4 flex justify-end">
                                    @can('approve', $task)
                                        @if($task->pending_review)
                                            <form action="{{ route('tasks.approve', $task) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-lg hover:bg-green-600 shadow">Setujui & Selesaikan</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>


                                {{-- Bagian Rincian Tugas (Subtask), Waktu, Lampiran, dan Komentar tetap sama --}}
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
                                
                                <div class="mt-4 border-t border-gray-200 pt-4" x-data="{ showManualForm: false }">
                                    <h5 class="font-semibold text-sm mb-2 text-gray-700">Pencatatan Waktu</h5>
                                    <div class="flex justify-between items-center text-sm">
                                        <div id="time-log-display-{{ $task->id }}">
                                            @php
                                                $totalMinutes = $task->timeLogs->sum('duration_in_minutes');
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            <p>Waktu Estimasi: <span class="font-bold">{{ (float)$task->estimated_hours ?? 0 }} jam</span></p>
                                            <p>Waktu Tercatat: <span class="font-bold text-blue-600">{{ $hours }} jam {{ $minutes }} menit</span></p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <template x-if="runningTaskGlobal !== {{ $task->id }}">
                                                <button @click="startTimer({{ $task->id }})" class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded hover:bg-green-600" :disabled="runningTaskGlobal !== null">
                                                    START
                                                </button>
                                            </template>
                                            <template x-if="runningTaskGlobal === {{ $task->id }}">
                                                 <button @click="stopTimer({{ $task->id }})" class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded hover:bg-red-600 animate-pulse">
                                                    STOP
                                                </button>
                                            </template>
                                            <button @click="showManualForm = !showManualForm" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded hover:bg-gray-300">MANUAL</button>
                                        </div>
                                    </div>
                                    <div x-show="showManualForm" x-transition class="mt-4 border-t border-gray-200 pt-4">
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
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <h5 class="font-semibold text-sm mb-2 text-gray-700">Diskusi</h5>
                                    <div class="space-y-3 mb-4">
                                        @forelse($task->comments as $comment)
                                        <div class="flex items-start space-x-2 text-sm">
                                            <span class="font-bold text-gray-800">{{ optional($comment->user)->name ?? 'User Dihapus' }}:</span>
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
                {{-- ... (Bagian Kolom Kanan tetap sama) ... --}}
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Distribusi Status Tugas</h3>
                    <canvas id="taskStatusChart"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Detail Proyek</h3>
                    <p class="text-gray-700">{{ $project->description }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Tim Proyek</h3>
                    <ul>
                        <li class="flex items-center space-x-2">
                            <span class="font-bold text-gray-700">Ketua Tim:</span>
                            <span>{{ optional($project->leader)->name ?? 'N/A' }}</span>
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
                    <ul class="space-y-3">
                        @foreach($project->activities->take(10) as $activity)
                            <li class="text-sm text-gray-600 border-b border-gray-200 pb-2">
                                <span class="font-semibold text-gray-800">{{ optional($activity->user)->name ?? 'User Telah Dihapus' }}</span>
                                @switch($activity->description)
                                    @case('created_project')
                                        membuat proyek ini
                                        @break
                                    @case('updated_project')
                                        memperbarui proyek ini
                                        @break
                                    @case('created_task')
                                        membuat tugas "{{ optional($activity->subject)->name ?? 'Tugas yang telah dihapus' }}"
                                        @break
                                    @case('updated_task')
                                        memperbarui tugas "{{ optional($activity->subject)->name ?? 'Tugas yang telah dihapus' }}"
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
    
    {{-- Memuat Tom Select JS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi Tom Select
            new TomSelect('#assignees',{
                plugins: ['remove_button'],
                create: false,
            });

            // Inisialisasi Chart.js
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
    </script>
</body>
</html>
