<x-app-layout>
    {{-- Slot untuk style tambahan jika diperlukan --}}
    <x-slot name="styles">
        <style>
            .sortable-ghost { opacity: 0.4; background: #e0e7ff; border: 2px dashed #6366f1; }
            #kanban-container::-webkit-scrollbar { height: 8px; }
            #kanban-container::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
            .kanban-column { min-height: 75vh; }
        </style>
    </x-slot>

    {{-- Header Halaman --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Papan Kanban Proyek: <span class="font-bold text-indigo-600">{{ $project->name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Geser kartu tugas antar kolom untuk memperbarui statusnya.</p>
            </div>
            <div>
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Kembali ke Detail Proyek
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Badan Papan Kanban --}}
    <div class="py-12" x-data="projectDetail()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="kanban-container" class="bg-white p-4 rounded-lg shadow-sm">
                <div id="kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $statuses = [
                            'pending'     => ['name' => 'Menunggu', 'color' => 'bg-gray-400', 'border' => 'border-gray-400'],
                            'in_progress' => ['name' => 'Dikerjakan', 'color' => 'bg-blue-500', 'border' => 'border-blue-500'],
                            'for_review'  => ['name' => 'Review', 'color' => 'bg-yellow-500', 'border' => 'border-yellow-500'],
                            'completed'   => ['name' => 'Selesai', 'color' => 'bg-green-500', 'border' => 'border-green-500']
                        ];
                    @endphp

                    @foreach ($statuses as $statusKey => $statusInfo)
                        <div class="bg-gray-100 rounded-lg flex-shrink-0 flex flex-col">
                            {{-- Header Kolom --}}
                            <div class="p-4 flex justify-between items-center border-b-2 {{ $statusInfo['border'] }}">
                                <h3 class="font-bold text-gray-700">{{ $statusInfo['name'] }}</h3>
                                <span class="text-sm font-semibold text-white {{ $statusInfo['color'] }} rounded-full px-3 py-1">{{ $groupedTasks[$statusKey]->count() }}</span>
                            </div>
                            
                            {{-- Area Kartu Tugas --}}
                            <div id="status-{{ $statusKey }}" data-status="{{ $statusKey }}" class="kanban-column p-4 space-y-4 flex-grow">
                                @forelse ($groupedTasks[$statusKey] as $task)
                                    <div id="task-{{ $task->id }}" data-task-id="{{ $task->id }}" class="task-card cursor-grab active:cursor-grabbing">
                                        {{-- Memanggil komponen task-card yang ringkas --}}
                                        <x-kanban-card :task="$task"/>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-400 py-10 px-4 flex flex-col items-center justify-center h-full">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12.75l9-9m0 0l-9 9m9-9v11.25m0-11.25h11.25m-11.25 0l9 9M3.75 12.75h11.25m-11.25 0l9 9m-9-9l-2.25-2.25M12 12l9 9" /></svg>
                                        <p class="font-medium">Kolom ini kosong.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            // Definisikan fungsi Alpine.js agar bisa diakses oleh komponen task-card
            function projectDetail() {
                return {
                    runningTaskGlobal: {{ optional(Auth::user()->timeLogs()->whereNull('end_time')->first())->task_id ?? 'null' }},
                    
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
                    async startTimer(taskId) { try { await this.postData(`/tasks/${taskId}/time-log/start`); this.runningTaskGlobal = taskId; location.reload(); } catch (error) { Swal.fire('Error', 'Gagal memulai timer: ' + error.message, 'error'); }},
                    async stopTimer(taskId) { try { await this.postData(`/tasks/${taskId}/time-log/stop`); this.runningTaskGlobal = null; location.reload(); } catch (error) { Swal.fire('Error', 'Gagal menghentikan timer: ' + error.message, 'error'); }}
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const showNotification = (icon, title, text = '') => {
                    Swal.fire({
                        icon: icon, title: title, text: text,
                        toast: true, position: 'top-end', showConfirmButton: false,
                        timer: 3500, timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                };

                const columns = document.querySelectorAll('.kanban-column');
                columns.forEach(column => {
                    new Sortable(column, {
                        group: 'kanban',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: function (evt) {
                            const taskEl = evt.item;
                            const fromColumn = evt.from;
                            const taskId = taskEl.dataset.taskId;
                            const newStatus = evt.to.dataset.status;
                            
                            taskEl.style.opacity = '0.5';

                            fetch(`{{ url('/tasks') }}/${taskId}/update-status`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ status: newStatus })
                            })
                            .then(response => {
                                if (!response.ok) { return response.json().then(err => Promise.reject(err)); }
                                return response.json();
                            })
                            .then(data => {
                                taskEl.style.opacity = '1';
                                showNotification('success', 'Status diperbarui!');
                            })
                            .catch(error => {
                                console.error('Gagal memindahkan kartu:', error);
                                fromColumn.insertBefore(taskEl, evt.from.children[evt.oldDraggableIndex]);
                                taskEl.style.opacity = '1';
                                showNotification('error', 'Gagal Memindahkan', error.message || 'Terjadi kesalahan server.');
                            });
                        }
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>