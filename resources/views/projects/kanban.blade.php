<x-app-layout>
    {{-- Slot untuk style tambahan jika diperlukan --}}
    <x-slot name="styles">
        <style>
            .sortable-ghost { 
                opacity: 0.4; 
                background: #e0e7ff; /* indigo-100 */
                border: 2px dashed #6366f1; /* indigo-500 */
                border-radius: 0.5rem; /* rounded-lg */
            }
            /* Scrollbar styling untuk board horizontal */
            #kanban-container::-webkit-scrollbar { 
                height: 12px; /* Lebih tebal */
            }
            #kanban-container::-webkit-scrollbar-track {
                background: #f1f1f1; /* Latar belakang track */
                border-radius: 10px;
            }
            #kanban-container::-webkit-scrollbar-thumb { 
                background-color: #9ca3af; /* gray-400 */
                border-radius: 10px; 
                border: 3px solid #f1f1f1; /* Padding sekitar thumb */
            }
            #kanban-container::-webkit-scrollbar-thumb:hover {
                background-color: #6b7280; /* gray-600 */
            }
            /* Ketinggian kolom Kanban agar tetap bisa di-drag */
            .kanban-column { 
                min-height: 70vh; /* Sedikit disesuaikan agar tidak terlalu memakan layar */
                /* max-height: calc(100vh - 300px); jika perlu batasan tinggi dengan scroll internal*/
                overflow-y: auto; /* Memungkinkan scroll jika kolom terlalu panjang */
            }
            .kanban-column::-webkit-scrollbar { width: 8px; }
            .kanban-column::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
            .drag-handle { cursor: grab; }
        </style>
    </x-slot>

    {{-- Header Halaman --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">Proyek: {{ $project->name }}</a> / 
                    <span class="font-bold">{{ __('Papan Kanban') }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Geser kartu tugas antar kolom untuk memperbarui statusnya.</p>
            </div>
            <div>
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Kembali: rounded-lg, shadow, hover scale --}}
                    <i class="fas fa-arrow-left mr-2 text-gray-600"></i> {{-- Icon Font Awesome --}}
                    Kembali ke Detail Proyek
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Badan Papan Kanban --}}
    <div class="py-12 bg-gray-50 flex-grow"> {{-- Latar belakang konsisten, flex-grow untuk mengisi ruang --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full"> {{-- Ensure container takes full height --}}
            {{-- Hapus h-full dari kanban-container --}}
            <div id="kanban-container" class="bg-white p-6 rounded-xl shadow-2xl overflow-x-auto flex flex-col"> {{-- Shadow-2xl, rounded-xl, flex-col untuk kolom --}}
                <div id="kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 flex-grow"> {{-- Grid dengan gap yang konsisten, flex-grow --}}
                    @php
                        $statuses = [
                            'pending'     => ['name' => 'Menunggu', 'color' => 'bg-gray-500', 'border' => 'border-gray-500', 'icon' => 'fas fa-hourglass-start'],
                            'in_progress' => ['name' => 'Dikerjakan', 'color' => 'bg-blue-600', 'border' => 'border-blue-600', 'icon' => 'fas fa-person-digging'],
                            'for_review'  => ['name' => 'Review', 'color' => 'bg-yellow-600', 'border' => 'border-yellow-600', 'icon' => 'fas fa-eye'],
                            'completed'   => ['name' => 'Selesai', 'color' => 'bg-green-600', 'border' => 'border-green-600', 'icon' => 'fas fa-check-double']
                        ];
                    @endphp

                    @foreach ($statuses as $statusKey => $statusInfo)
                        <div class="bg-gray-100 rounded-xl shadow-lg flex-shrink-0 flex flex-col"> {{-- Kolom: rounded-xl, shadow-lg --}}
                            {{-- Header Kolom --}}
                            <div class="p-4 flex justify-between items-center border-b-4 {{ $statusInfo['border'] }} rounded-t-xl bg-gray-200"> {{-- Border lebih tebal, rounded-t-xl, bg-gray-200 --}}
                                <h3 class="font-bold text-lg text-gray-700 flex items-center">
                                    <i class="{{ $statusInfo['icon'] }} mr-2 {{ $statusInfo['color'] == 'bg-gray-500' ? 'text-gray-600' : ($statusInfo['color'] == 'bg-blue-600' ? 'text-blue-700' : ($statusInfo['color'] == 'bg-yellow-600' ? 'text-yellow-700' : 'text-green-700')) }}"></i> {{-- Menambahkan ikon ke header kolom --}}
                                    {{ $statusInfo['name'] }}
                                </h3>
                                <span class="text-sm font-bold text-white {{ $statusInfo['color'] }} rounded-full px-3 py-1 shadow-md"> {{-- Badge count: font-bold, shadow-md --}}
                                    {{ $groupedTasks[$statusKey]->count() }}
                                </span>
                            </div>
                            
                            {{-- Area Kartu Tugas --}}
                            {{-- Tambahkan flex flex-col di sini --}}
                            <div id="status-{{ $statusKey }}" data-status="{{ $statusKey }}" class="kanban-column p-4 space-y-4 flex flex-col flex-grow"> {{-- flex-grow agar kolom mengisi sisa ruang --}}
                                @forelse ($groupedTasks[$statusKey] as $task)
                                    <x-kanban-card :task="$task" id="task-{{ $task->id }}" data-task-id="{{ $task->id }}" class="task-card"/>
                                @empty
                                    <div class="text-center text-gray-400 py-10 px-4 flex flex-col items-center justify-center min-h-[150px] border-2 border-dashed border-gray-300 rounded-lg"> {{-- Pesan kolom kosong lebih modern --}}
                                        <i class="fas fa-box-open fa-2x text-gray-300 mb-4"></i> {{-- Icon baru --}}
                                        <p class="font-medium text-sm">Kolom ini kosong.</p>
                                        <p class="text-xs mt-1">Seret tugas ke sini.</p>
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
                        // Menambahkan handle untuk drag-and-drop
                        // Ini akan mencari elemen dengan kelas .drag-handle di dalam setiap item
                        handle: '.drag-handle', 
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
                                // Opsional: Perbarui teks counter di header kolom
                                const newColumnCountSpan = evt.to.closest('.flex-col').querySelector('span');
                                const oldColumnCountSpan = evt.from.closest('.flex-col').querySelector('span');
                                if(newColumnCountSpan) {
                                    newColumnCountSpan.textContent = parseInt(newColumnCountSpan.textContent) + 1;
                                }
                                if(oldColumnCountSpan) {
                                    oldColumnCountSpan.textContent = parseInt(oldColumnCountSpan.textContent) - 1;
                                }
                            })
                            .catch(error => {
                                console.error('Gagal memindahkan kartu:', error);
                                // Kembalikan kartu ke posisi semula secara visual jika ada error
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