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
                    <div class="border-b border-gray-200 mb-4"><nav class="-mb-px flex space-x-8" aria-label="Tabs"><button @click="activeTab = 'tasks'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'tasks', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tasks' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Daftar Tugas</button></nav></div>
                    <div>
                        <div x-show="activeTab === 'tasks'" x-cloak><div class="space-y-4">@forelse($project->tasks()->orderBy('deadline', 'asc')->get() as $task)<x-task-card :task="$task"/>@empty<p class="text-gray-500 text-center py-8">Belum ada tugas di proyek ini.</p>@endforelse</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script>
            function projectDetail() {
                return {
                    runningTaskGlobal: {{ optional(Auth::user()->timeLogs()->whereNull('end_time')->first())->task_id ?? 'null' }},
                    activeTab: 'tasks',
                    isChartInitialized: false,
                    init() {
                        this.initTomSelect();
                        this.$watch('activeTab', value => {
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
    </x-slot>
</x-app-layout>