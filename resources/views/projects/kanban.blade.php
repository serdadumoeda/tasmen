<x-app-layout>
    {{-- Menambahkan style khusus untuk Papan Kanban --}}
    <x-slot name="styles">
        <style>
            /* Efek visual saat kartu digeser */
            .sortable-ghost {
                opacity: 0.4;
                background: #e0e7ff; /* light indigo */
                border: 2px dashed #6366f1; /* indigo */
            }
            /* Kustomisasi scrollbar horizontal */
            #kanban-container::-webkit-scrollbar {
                height: 8px;
            }
            #kanban-container::-webkit-scrollbar-thumb {
                background-color: #d1d5db; /* gray-300 */
                border-radius: 10px;
            }
            /* Style tambahan untuk memastikan kartu punya tinggi minimal */
            .kanban-column {
                min-height: 75vh;
            }
        </style>
    </x-slot>

    {{-- Header Papan Kanban yang Informatif --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-grow">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Papan Kanban Proyek:
                    <span class="font-bold text-indigo-600">{{ $project->name }}</span>
                </h2>
                <p class="text-sm text-gray-600 mt-1 max-w-2xl">
                    {{ Str::limit($project->description, 150) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-sm">
                    <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Kembali ke Detail Proyek
                </a>
            </div>
        </div>
    </x-slot>

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
                        {{-- Header Kolom --}}
                        <div class="p-4 flex justify-between items-center border-b-2 {{ $statusInfo['border'] }}">
                            <h3 class="font-bold text-gray-700">{{ $statusInfo['name'] }}</h3>
                            <span class="text-sm font-semibold text-white {{ $statusInfo['color'] }} rounded-full px-3 py-1">
                                {{ $groupedTasks[$statusKey]->count() }}
                            </span>
                        </div>
                        
                        {{-- Area Kartu Tugas --}}
                        <div id="status-{{ $statusKey }}" data-status="{{ $statusKey }}" class="kanban-column p-4 space-y-4 flex-grow">
                            
                            @forelse ($groupedTasks[$statusKey] as $task)
                                @php
                                    $isOverdue = $task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast() && $task->status !== 'completed';
                                @endphp
                                
                                {{-- ========================================================== --}}
                                {{-- =============      DESAIN KARTU TUGAS MODERN      ============ --}}
                                {{-- ========================================================== --}}
                                <div id="task-{{ $task->id }}" data-task-id="{{ $task->id }}" 
                                     class="bg-white rounded-lg shadow-sm border {{ $isOverdue ? 'border-l-4 border-l-red-500' : 'border-gray-200' }} p-4 cursor-grab active:cursor-grabbing hover:shadow-md hover:border-indigo-500 transition-all duration-300 ease-in-out flex flex-col space-y-4">
                                    
                                    {{-- 1. Header Kartu: Prioritas & Judul --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            @php $priorityColor = $task->priority === 'high' ? 'bg-red-100 text-red-800' : ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $priorityColor }}">{{ ucfirst($task->priority) }}</span>
                                            @if($isOverdue)
                                                <div title="Tugas ini terlambat!" class="flex items-center text-red-500">
                                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 011-1h.008a1 1 0 011 1v3.008a1 1 0 01-1 1H9a1 1 0 01-1-1V5z" clip-rule="evenodd" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="font-bold text-gray-800">{{ $task->title }}</p>
                                    </div>

                                    {{-- 2. Metrik Tugas: Deadline, Subtugas, Komentar --}}
                                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-600 border-t pt-3">
                                        @if($task->deadline)
                                            <div class="flex items-center" title="Deadline">
                                                <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                <span>{{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center" title="Rincian Tugas">
                                            <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                            <span>{{ $task->subTasks->where('is_completed', true)->count() }}/{{ $task->subTasks->count() }}</span>
                                        </div>
                                        <div class="flex items-center" title="Komentar">
                                            <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            <span>{{ $task->comments->count() }}</span>
                                        </div>
                                    </div>
                                    
                                    {{-- 3. Pengerja & Progress --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex -space-x-2">
                                            @foreach($task->assignees as $assignee)
                                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" 
                                                     src="https://ui-avatars.com/api/?name={{ urlencode($assignee->name) }}&background=random&color=fff&font-size=0.5" 
                                                     alt="{{ $assignee->name }}"
                                                     title="{{ $assignee->name }}">
                                            @endforeach
                                        </div>
                                        <div class="w-1/3 flex items-center">
                                            <span class="text-xs font-semibold text-gray-500 mr-2">{{ $task->progress }}%</span>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $task->progress }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                {{-- Tampilan Kolom Kosong --}}
                                <div class="text-center text-gray-400 py-10 px-4 flex flex-col items-center justify-center h-full">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12.75l9-9m0 0l-9 9m9-9v11.25m0-11.25h11.25m-11.25 0l9 9M3.75 12.75h11.25m-11.25 0l9 9m-9-9l-2.25-2.25M12 12l9 9" />
                                    </svg>
                                    <p class="font-medium">Kolom ini kosong.</p>
                                    <p class="text-sm">Geser tugas ke sini untuk memulai.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Script Notifikasi (Tidak Berubah) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const columns = document.querySelectorAll('.kanban-column');
            
            const revertCard = (taskEl, fromColumn, oldIndex) => {
                fromColumn.insertBefore(taskEl, fromColumn.children[oldIndex]);
                taskEl.style.opacity = '1';
            };

            const showNotification = (icon, title, text) => {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    timer: 3500, // sedikit lebih lama untuk pesan info
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            };

            columns.forEach(column => {
                new Sortable(column, {
                    group: 'kanban',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function (evt) {
                        const taskEl = evt.item;
                        const toColumn = evt.to;
                        const fromColumn = evt.from;
                        const taskId = taskEl.dataset.taskId;
                        const newStatus = toColumn.dataset.status;
                        const oldIndex = evt.oldDraggableIndex;
                        
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
                            if (response.status === 403) {
                                revertCard(taskEl, fromColumn, oldIndex);
                                showNotification('error', 'Akses Ditolak!', 'Anda tidak punya hak untuk memindahkan tugas ini.');
                                return Promise.reject('Forbidden');
                            }
                            if (!response.ok) {
                                revertCard(taskEl, fromColumn, oldIndex);
                                showNotification('error', 'Terjadi Kesalahan', 'Gagal memperbarui status karena masalah server.');
                                return Promise.reject('Server Error');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.special_case === 'moved_to_review') {
                                showNotification('info', 'Tugas Menunggu Review', 'Tugas dipindahkan ke kolom Review untuk diperiksa.');
                            } else {
                                showNotification('success', 'Berhasil!', 'Status tugas telah diperbarui.');
                            }

                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        })
                        .catch(error => {
                            if (error !== 'Forbidden' && error !== 'Server Error') {
                                revertCard(taskEl, fromColumn, oldIndex);
                                showNotification('error', 'Koneksi Gagal', 'Periksa koneksi internet Anda.');
                            }
                            console.error('Error:', error);
                        });
                    }
                });
            });
        });
    </script>
</x-app-layout>