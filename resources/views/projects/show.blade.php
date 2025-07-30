<x-app-layout>
    {{-- Slot untuk memuat CSS khusus halaman ini --}}
    <x-slot name="styles">
        <style>
            .progress-bar { transition: width 0.6s ease; }
            [x-cloak] { display: none !important; }
            
            /* Style kustom untuk Tom Select (menggunakan tema default) */
            .ts-control {
                border-radius: 0.375rem;
                border-color: #d1d5db;
                padding: 0.5rem 0.75rem;
            }
            .ts-control .item {
                background-color: #00796B;
                color: white;
                border-radius: 0.25rem;
                font-weight: 500;
                padding: 0.25rem 0.5rem;
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div><h2 class="font-semibold text-xl text-gray-800 leading-tight"><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Proyek</a> / <span class="font-bold">{{ $project->name }}</span></h2><p class="text-sm text-gray-500 mt-1">Detail dan progres proyek.</p></div>
            <div class="flex items-center justify-start sm:justify-end flex-wrap gap-2">
                <x-dropdown align="right" width="60"><x-slot name="trigger"><button class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition ease-in-out duration-150"><div>Tampilan & Laporan</div><div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div></button></x-slot><x-slot name="content"><x-dropdown-link :href="route('projects.kanban', $project)">Papan Kanban</x-dropdown-link><x-dropdown-link :href="route('projects.calendar', $project)">Kalender</x-dropdown-link>@if($project->start_date && $project->end_date)<x-dropdown-link :href="route('projects.s-curve', $project)">Kurva S</x-dropdown-link>@endif @can('viewTeamDashboard', $project)<x-dropdown-link :href="route('projects.team.dashboard', $project)">Dashboard Tim</x-dropdown-link>@endcan @if(in_array(optional(Auth::user())->role, ['superadmin', 'Eselon I', 'Eselon II']))<div class="border-t border-gray-200"></div><x-dropdown-link :href="route('projects.report', $project)" target="_blank">Laporan PDF</x-dropdown-link>@endif</x-slot></x-dropdown>
                @can('update', $project)<a href="{{ route('projects.budget-items.index', $project) }}" class="inline-block bg-green-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-green-700">Anggaran</a><a href="{{ route('projects.edit', $project) }}" class="inline-block bg-amber-500 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-amber-600">Edit Proyek</a>@endcan
            </div>
        </div>
    </x-slot>
    
    <div x-data="projectDetail()">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div><h2 class="text-2xl font-semibold text-gray-700 mb-4">Ringkasan Proyek</h2><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"><div class="bg-white p-4 rounded-lg shadow text-center"><p class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</p><p class="text-gray-500">Total Tugas</p></div><div class="bg-white p-4 rounded-lg shadow text-center"><p class="text-3xl font-bold text-yellow-500">{{ $stats['pending'] }}</p><p class="text-gray-500">Tugas Menunggu</p></div><div class="bg-white p-4 rounded-lg shadow text-center"><p class="text-3xl font-bold text-orange-500">{{ $stats['in_progress'] }}</p><p class="text-gray-500">Dikerjakan</p></div><div class="bg-white p-4 rounded-lg shadow text-center"><p class="text-3xl font-bold text-green-500">{{ $stats['completed'] }}</p><p class="text-gray-500">Selesai</p></div></div></div>
                <div class="bg-white p-4 sm:p-6 rounded-lg shadow">
                    <div class="border-b border-gray-200 mb-4"><nav class="-mb-px flex space-x-8" aria-label="Tabs"><button @click="activeTab = 'tasks'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'tasks', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tasks' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Daftar Tugas</button><button @click="activeTab = 'info'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'info', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'info' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Informasi & Aktivitas</button>@can('update', $project)<button @click="activeTab = 'add'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'add', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'add' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Tambah Tugas Baru</button>@endcan</nav></div>
                    <div>
                        <div x-show="activeTab === 'tasks'" x-cloak><div class="space-y-4">@forelse($project->tasks()->orderBy('deadline', 'asc')->get() as $task)<x-task-card :task="$task"/>@empty<p class="text-gray-500 text-center py-8">Belum ada tugas di proyek ini.</p>@endforelse</div></div>
                        <div x-show="activeTab === 'info'" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Distribusi Status Tugas</h3>
                                        <canvas id="taskStatusChart"></canvas>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Tim Proyek</h3>
                                        <ul><li class="flex items-center space-x-2"><span class="font-bold text-gray-700">Ketua Tim:</span><span>{{ optional($project->leader)->name ?? 'N/A' }}</span></li></ul>
                                        <h4 class="font-semibold mt-4 text-gray-800">Anggota:</h4>
                                        <ul class="list-disc list-inside mt-2 text-gray-700">
                                            @foreach($project->members as $member)
                                                <li>{{ $member->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    {{-- --- AWAL PENAMBAHAN BAGIAN RIWAYAT PEMINJAMAN --- --}}
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Riwayat Permintaan Peminjaman</h3>
                                        <div class="space-y-4">
                                            @forelse($loanRequests as $request)
                                                <div class="border-l-4 @if($request->status == 'approved') border-green-500 @elseif($request->status == 'rejected') border-red-500 @else border-yellow-500 @endif bg-white p-3 rounded-r-lg shadow-sm">
                                                    <p class="text-sm font-medium text-gray-800">
                                                        Permintaan untuk <strong>{{ $request->requestedUser?->name ?? 'N/A' }}</strong>
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        Oleh: {{ $request->requester?->name ?? 'N/A' }}
                                                        <span class="mx-1">|</span>
                                                        {{ $request->created_at->diffForHumans() }}
                                                    </p>
                                                    <div class="mt-2">
                                                        @if ($request->status == 'approved')
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui oleh {{ $request->approver?->name ?? 'N/A' }}</span>
                                                        @elseif ($request->status == 'pending')
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu persetujuan {{ $request->approver?->name ?? 'N/A' }}</span>
                                                        @elseif ($request->status == 'rejected')
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak oleh {{ $request->approver?->name ?? 'N/A' }}</span>
                                                            @if($request->rejection_reason)
                                                                <p class="text-xs text-gray-600 mt-1 italic">"{{ $request->rejection_reason }}"</p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500">Tidak ada riwayat permintaan peminjaman untuk proyek ini.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                    {{-- --- AKHIR PENAMBAHAN BAGIAN RIWAYAT PEMINJAMAN --- --}}

                                </div>
                                <div class="space-y-6">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Detail Proyek</h3>
                                        <p class="text-gray-700">{{ $project->description }}</p>
                                        <div class="text-sm mt-4 grid grid-cols-2 gap-2 text-gray-600">
                                            <p>Dibuat Oleh:</p><p class="font-semibold text-gray-800">{{ $project->owner->name ?? 'N/A' }}</p>
                                            <p>Tanggal Mulai:</p><p class="font-semibold text-gray-800">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}</p>
                                            <p>Tanggal Selesai:</p><p class="font-semibold text-gray-800">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-800">Aktivitas Terbaru</h3>
                                        <ul class="space-y-3">
                                            @foreach($project->activities->take(5) as $activity)
                                            <li class="text-sm text-gray-600 border-b pb-2">
                                                <span class="font-semibold text-gray-800">{{ optional($activity->user)->name ?? 'User Telah Dihapus' }}</span>
                                                @switch($activity->description)
                                                    @case('created_project')membuat proyek ini @break
                                                    @case('updated_project')memperbarui proyek ini @break
                                                    @case('created_task')membuat tugas "{{ optional($activity->subject)->title ?? '...' }}" @break
                                                    @case('updated_task')memperbarui tugas "{{ optional($activity->subject)->title ?? '...' }}" @break
                                                    @case('deleted_task')menghapus sebuah tugas @break
                                                    @default melakukan sebuah aktivitas @endswitch
                                                <span class="block text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-show="activeTab === 'add'" x-cloak><form action="{{ route('tasks.store', $project) }}" method="POST"><div class="space-y-4">@csrf<div><label for="add_title" class="block text-sm font-medium text-gray-700">Judul Tugas</label><input type="text" name="title" id="add_title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('title') }}" required></div><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div><label for="add_deadline" class="block text-sm font-medium text-gray-700">Deadline</label><input type="date" name="deadline" id="add_deadline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('deadline') }}"></div><div><label for="add_estimated_hours" class="block text-sm font-medium text-gray-700">Estimasi Jam</label><input type="number" step="0.5" name="estimated_hours" id="add_estimated_hours" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('estimated_hours') }}"></div><div><label for="add_priority" class="block text-sm font-medium text-gray-700">Prioritas</label><select name="priority" id="add_priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="low">Rendah</option><option value="medium" selected>Sedang</option><option value="high">Tinggi</option></select></div></div><div><label for="add_assignees" class="block text-sm font-medium text-gray-700">Tugaskan Kepada</label><select name="assignees[]" id="add_assignees" multiple>@foreach($projectMembers as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></div></div><button type="submit" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Simpan Tugas</button></form></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function projectDetail() {
            return {
                runningTaskGlobal: {{ optional(Auth::user()->timeLogs()->whereNull('end_time')->first())->task_id ?? 'null' }},
                activeTab: 'tasks',
                isChartInitialized: false,
                init() {
                    console.log('Initializing projectDetail component...');
                    this.initTomSelect();
                    this.$watch('activeTab', value => {
                        console.log('activeTab changed to:', value);
                        if (value === 'info' && !this.isChartInitialized) {
                            this.initChart();
                        }
                    });
                    // Jika ada hash di URL, coba aktifkan tab yang sesuai
                    if (window.location.hash) {
                        const hash = window.location.hash.substring(1);
                        if (hash === 'info') {
                            this.activeTab = 'info';
                        }
                    }
                },
                initTomSelect() {
                     new TomSelect('#add_assignees', { plugins: ['remove_button'], create: false });
                },
                initChart() {
                    const ctx = document.getElementById('taskStatusChart');
                    if (ctx) {
                        const stats = @json($stats);
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Menunggu', 'Dikerjakan', 'Selesai'],
                                datasets: [{
                                    data: [stats.pending, stats.in_progress, stats.completed],
                                    backgroundColor: ['#facc15', '#f97316', '#22c55e'],
                                    hoverOffset: 4,
                                    borderColor: '#fff',
                                }]
                            },
                            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                        });
                        this.isChartInitialized = true;
                    }
                },
                async postData(url) {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({ message: 'Terjadi kesalahan pada server.' }));
                        throw new Error(errorData.message);
                    }
                    return response.json();
                },

                async startTimer(taskId) {
                    try {
                        await this.postData(`/tasks/${taskId}/time-log/start`);
                        this.runningTaskGlobal = taskId;
                        location.reload();
                    } catch (error) {
                        alert('Gagal memulai timer: ' + error.message);
                    }
                },

                async stopTimer(taskId) {
                    try {
                        await this.postData(`/tasks/${taskId}/time-log/stop`);
                        this.runningTaskGlobal = null;
                        location.reload();
                    } catch (error) {
                        alert('Gagal menghentikan timer: ' + error.message);
                    }
                }
            }
        }
    </script>
</x-app-layout>