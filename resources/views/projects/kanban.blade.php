<x-app-layout>
    {{-- Style untuk Papan Kanban --}}
    <x-slot name="styles">
        <style>
            .sortable-ghost { opacity: 0.4; background: #e0e7ff; border: 2px dashed #6366f1; }
            #kanban-container::-webkit-scrollbar { height: 8px; }
            #kanban-container::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
            .kanban-column { min-height: 75vh; }
            .subtask-completed { text-decoration: line-through; color: #9ca3af; }
        </style>
    </x-slot>

    {{-- Header Halaman --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Papan Kanban Proyek: <span class="font-bold text-indigo-600">{{ $project->name }}</span>
                </h2>
                <p class="text-sm text-gray-600 mt-1 max-w-2xl">{{ Str::limit($project->description, 150) }}</p>
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
    <div class="py-2 sm:py-6">
        <div id="kanban-container" class="max-w-full mx-auto overflow-x-auto">
            <div id="kanban-board" class="inline-flex space-x-4 p-4 min-w-full">
                @php
                    $statuses = [
                        'pending'     => ['name' => 'Menunggu', 'color' => 'bg-gray-400', 'border' => 'border-gray-400'],
                        'in_progress' => ['name' => 'Dikerjakan', 'color' => 'bg-blue-500', 'border' => 'border-blue-500'],
                        'for_review'  => ['name' => 'Review', 'color' => 'bg-yellow-500', 'border' => 'border-yellow-500'],
                        'completed'   => ['name' => 'Selesai', 'color' => 'bg-green-500', 'border' => 'border-green-500']
                    ];
                @endphp

                @foreach ($statuses as $statusKey => $statusInfo)
                    <div class="bg-gray-100 rounded-lg w-80 sm:w-96 flex-shrink-0 shadow-sm flex flex-col">
                        <div class="p-4 flex justify-between items-center border-b-2 {{ $statusInfo['border'] }}">
                            <h3 class="font-bold text-gray-700">{{ $statusInfo['name'] }}</h3>
                            <span class="text-sm font-semibold text-white {{ $statusInfo['color'] }} rounded-full px-3 py-1">{{ $groupedTasks[$statusKey]->count() }}</span>
                        </div>
                        
                        <div id="status-{{ $statusKey }}" data-status="{{ $statusKey }}" class="kanban-column p-4 space-y-4 flex-grow">
                            @forelse ($groupedTasks[$statusKey] as $task)
                                <div id="task-{{ $task->id }}" data-task-id="{{ $task->id }}" class="task-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-grab active:cursor-grabbing hover:shadow-md transition-all flex flex-col space-y-4">
                                    <p class="font-bold text-gray-800">{{ $task->title }}</p>
                                    
                                    @if($task->subTasks->isNotEmpty())
                                    <div class="border-t pt-3 space-y-2">
                                        <div class="flex justify-between items-center text-sm"><h4 class="font-medium text-gray-600">Rincian Tugas:</h4><span id="subtask-counter-{{ $task->id }}" class="text-gray-500 font-semibold">{{ $task->subTasks->where('is_completed', true)->count() }}/{{ $task->subTasks->count() }}</span></div>
                                        <div class="space-y-1">
                                            @foreach($task->subTasks as $subTask)
                                            <label for="subtask-{{ $subTask->id }}" class="flex items-center text-sm text-gray-700 hover:bg-gray-50 p-1 rounded cursor-pointer">
                                                <input type="checkbox" id="subtask-{{ $subTask->id }}" data-subtask-id="{{ $subTask->id }}" class="subtask-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @if($subTask->is_completed) checked @endif>
                                                <span class="ml-2 {{ $subTask->is_completed ? 'subtask-completed' : '' }}">{{ $subTask->title }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="flex items-center justify-between pt-3 border-t">
                                        <div class="flex -space-x-2">
                                            @foreach($task->assignees as $assignee)
                                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" src="https://ui-avatars.com/api/?name={{ urlencode($assignee->name) }}&background=random&color=fff&font-size=0.5" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
                                            @endforeach
                                        </div>
                                        <div class="w-1/2 flex items-center">
                                            <span id="progress-text-{{ $task->id }}" class="text-xs font-semibold text-gray-500 mr-2">{{ $task->progress }}%</span>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5"><div id="progress-bar-{{ $task->id }}" class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $task->progress }}%"></div></div>
                                        </div>
                                    </div>
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

    {{-- Pustaka dan Skrip Kustom --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
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
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ status: newStatus })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(data => {
                        taskEl.style.opacity = '1';
                        showNotification('success', 'Status diperbarui!');
                        // Tidak perlu reload, kartu sudah di tempat yang benar
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

        document.querySelectorAll('.subtask-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const subTaskId = this.dataset.subtaskId;
                const taskCard = this.closest('.task-card');
                const taskId = taskCard.dataset.taskId;
                const labelSpan = this.nextElementSibling;
                
                labelSpan.classList.toggle('subtask-completed');
                fetch(`/subtasks/${subTaskId}/toggle`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                })
                .then(response => response.ok ? response.json() : Promise.reject('Update gagal'))
                .then(data => {
                    document.getElementById(`progress-bar-${taskId}`).style.width = data.task_progress + '%';
                    document.getElementById(`progress-text-${taskId}`).innerText = data.task_progress + '%';
                    document.getElementById(`subtask-counter-${taskId}`).innerText = `${data.completed_subtasks}/${data.total_subtasks}`;
                    showNotification('success', 'Progress diperbarui!');
                })
                .catch(error => {
                    this.checked = !this.checked;
                    labelSpan.classList.toggle('subtask-completed');
                    showNotification('error', 'Gagal update.');
                    console.error('Error:', error);
                });
            });
        });
    });
    </script>
</x-app-layout>