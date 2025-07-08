<x-app-layout>
    {{-- Menambahkan style khusus untuk Papan Kanban --}}
    <x-slot name="styles">
        <style>
            /* Memberi efek visual yang lebih baik saat kartu digeser */
            .sortable-ghost {
                opacity: 0.4;
                background: #e0e7ff; /* light indigo */
                border: 2px dashed #6366f1; /* indigo */
            }
            /* Memastikan area scroll terlihat lebih baik dan tidak mengganggu */
            #kanban-container::-webkit-scrollbar {
                height: 8px;
            }
            #kanban-container::-webkit-scrollbar-thumb {
                background-color: #d1d5db; /* gray-300 */
                border-radius: 10px;
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Papan Kanban: <span class="text-indigo-600">{{ $project->name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Geser kartu tugas antar kolom untuk memperbarui statusnya.</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="mt-2 sm:mt-0 text-sm inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-sm">
                <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Tampilan Daftar
            </a>
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
                    {{-- KOLOM STATUS --}}
                    <div class="bg-gray-100 rounded-lg w-80 sm:w-96 flex-shrink-0 shadow-sm">
                        {{-- Header Kolom --}}
                        <div class="p-4 flex justify-between items-center border-b-2 {{ $statusInfo['border'] }}">
                            <h3 class="font-bold text-gray-700">{{ $statusInfo['name'] }}</h3>
                            <span class="text-sm font-semibold text-white {{ $statusInfo['color'] }} rounded-full px-3 py-1">
                                {{ $groupedTasks[$statusKey]->count() }}
                            </span>
                        </div>
                        {{-- Area Kartu Tugas --}}
                        <div id="status-{{ $statusKey }}" data-status="{{ $statusKey }}" class="kanban-column p-4 min-h-[600px] space-y-4">
                            @forelse ($groupedTasks[$statusKey] as $task)
                                <div id="task-{{ $task->id }}" data-task-id="{{ $task->id }}" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 cursor-grab active:cursor-grabbing hover:shadow-lg hover:border-indigo-500 transition-all duration-300 ease-in-out">
                                    {{-- Detail Kartu (tidak diubah) --}}
                                    <div class="mb-3">
                                        @php $priorityColor = $task->priority === 'high' ? 'bg-red-100 text-red-800' : ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $priorityColor }}">{{ ucfirst($task->priority) }}</span>
                                        <p class="font-bold text-lg text-gray-800 mt-2">{{ $task->name }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-600 mb-4 border-t pt-3">
                                        @if($task->deadline)
                                            <div class="flex items-center" title="Deadline">
                                                <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                <span>{{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                                            </div>
                                        @endif
                                        @if($task->subTasks->count() > 0)
                                            <div class="flex items-center" title="Progres Rincian Tugas">
                                                <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                                <span>{{ $task->subTasks->where('is_completed', true)->count() }} dari {{ $task->subTasks->count() }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex -space-x-2">
                                            @foreach($task->assignees as $assignee)
                                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" 
                                                     src="https://ui-avatars.com/api/?name={{ urlencode($assignee->name) }}&background=random&color=fff&font-size=0.5" 
                                                     alt="{{ $assignee->name }}"
                                                     title="{{ $assignee->name }}">
                                            @endforeach
                                        </div>
                                        <div class="w-1/2 flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $task->progress }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-500">{{ $task->progress }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-sm text-gray-400 py-10 border-2 border-dashed rounded-lg">
                                    <p>Tidak ada tugas di sini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- =============      KODE BARU: PUSTAKA & SCRIPT NOTIFIKASI     ============ --}}
    {{-- =================================================================== --}}
    
    {{-- 1. Tambahkan pustaka SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    {{-- 2. Logika JavaScript yang diperbarui dengan notifikasi --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const columns = document.querySelectorAll('.kanban-column');
            
            // Fungsi untuk mengembalikan kartu jika terjadi error
            const revertCard = (taskEl, fromColumn, oldIndex) => {
                fromColumn.insertBefore(taskEl, fromColumn.children[oldIndex]);
                taskEl.style.opacity = '1';
            };

            // Fungsi untuk menampilkan notifikasi menggunakan SweetAlert2
            const showNotification = (icon, title, text) => {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
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
                        
                        // Beri efek visual saat proses update
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
                            // Cek status HTTP untuk menentukan notifikasi yang tepat
                            if (response.status === 403) {
                                revertCard(taskEl, fromColumn, oldIndex);
                                showNotification('error', 'Akses Ditolak!', 'Anda tidak punya hak untuk memindahkan tugas ini. Hanya pemilik, pimpinan, atau penerima tugas yang bisa.');
                                return Promise.reject('Forbidden'); // Hentikan proses
                            }
                            if (!response.ok) {
                                revertCard(taskEl, fromColumn, oldIndex);
                                showNotification('error', 'Terjadi Kesalahan', 'Gagal memperbarui status karena masalah pada server.');
                                return Promise.reject('Server Error'); // Hentikan proses
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Jika respons mengandung pesan khusus untuk review
                            if (data.special_case === 'moved_to_review') {
                                showNotification('info', 'Tugas Menunggu Review', 'Tugas telah selesai dan dipindahkan ke kolom Review untuk diperiksa oleh pimpinan.');
                            } else {
                                showNotification('success', 'Berhasil!', 'Status tugas telah diperbarui.');
                            }

                            // Muat ulang halaman untuk sinkronisasi data
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        })
                        .catch(error => {
                            // Error akan ditangani di blok .then(), bagian ini untuk error jaringan
                            if (error !== 'Forbidden' && error !== 'Server Error') {
                                revertCard(taskEl, fromColumn, oldIndex);
                                showNotification('error', 'Koneksi Gagal', 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
                            }
                            console.error('Error:', error);
                        });
                    }
                });
            });
        });
    </script>
</x-app-layout>