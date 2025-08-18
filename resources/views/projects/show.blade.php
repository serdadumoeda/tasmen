<x-app-layout>
    {{-- Slot untuk memuat CSS khusus halaman ini --}}
    <x-slot name="styles">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
        <style>
            .progress-bar { transition: width 0.6s ease; }
            [x-cloak] { display: none !important; }
            
            /* Style kustom untuk Tom Select (menggunakan tema default) */
            .ts-control {
                border-radius: 0.5rem; /* rounded-lg */
                border-color: #d1d5db; /* gray-300 */
                padding: 0.5rem 0.75rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
            .ts-control.focus {
                border-color: #6366f1; /* indigo-500 */
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); /* ring-indigo-500 */
            }
            .ts-control .item {
                background-color: #00796B; /* Warna hijau gelap */
                color: white;
                border-radius: 0.25rem;
                font-weight: 500;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem;
            }
            .ts-control .item.active {
                background-color: #04655A; /* Sedikit lebih gelap saat aktif */
            }
            .ts-control .remove {
                color: white;
                opacity: 0.8;
            }
            .ts-control .remove:hover {
                color: white;
                opacity: 1;
            }
            .ts-dropdown {
                border-radius: 0.5rem; /* rounded-lg */
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-lg */
            }
            .ts-dropdown .option.active {
                background-color: #e0e7ff; /* indigo-100 */
                color: #1e3a8a; /* indigo-900 */
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">Kegiatan</a> /
                    <span class="font-bold">{{ $project->name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Detail dan progres kegiatan.</p>
            </div>
            <div class="flex items-center justify-start sm:justify-end flex-wrap gap-2">
                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Dropdown: rounded-lg, shadow, hover scale --}}
                            <div><i class="fas fa-eye mr-2"></i> Tampilan & Laporan</div>
                            <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10"> {{-- Dropdown Content: rounded-xl, shadow-2xl --}}
                            <x-dropdown-link :href="route('projects.kanban', $project)" class="hover:bg-gray-100 transition-colors duration-100"><i class="fas fa-th-large w-4 mr-2 text-gray-600"></i> Papan Kanban</x-dropdown-link>
                            <x-dropdown-link :href="route('projects.calendar', $project)" class="hover:bg-gray-100 transition-colors duration-100"><i class="fas fa-calendar-alt w-4 mr-2 text-gray-600"></i> Kalender</x-dropdown-link>
                            @php
                                $user = Auth::user();
                                $canViewSCurve = ($project->start_date && $project->end_date) ||
                                                 ($user && (
                                                     $user->role === \App\Models\User::ROLE_SUPERADMIN ||
                                                     $user->role === \App\Models\User::ROLE_ESELON_I ||
                                                     $user->role === \App\Models\User::ROLE_ESELON_II ||
                                                     $user->id === $project->owner_id
                                                 ));
                            @endphp
                            @if($canViewSCurve)
                                <x-dropdown-link :href="route('projects.s-curve', $project)" class="hover:bg-gray-100 transition-colors duration-100"><i class="fas fa-chart-area w-4 mr-2 text-gray-600"></i> Kurva S</x-dropdown-link>
                            @endif
                            @can('viewTeamDashboard', $project)<x-dropdown-link :href="route('projects.team.dashboard', $project)" class="hover:bg-gray-100 transition-colors duration-100"><i class="fas fa-users-viewfinder w-4 mr-2 text-gray-600"></i> Dashboard Tim</x-dropdown-link>@endcan 
                            @if(in_array(optional(Auth::user())->role, ['superadmin', 'Eselon I', 'Eselon II']))<div class="border-t border-gray-200"></div><x-dropdown-link :href="route('projects.report', $project)" target="_blank" class="hover:bg-gray-100 transition-colors duration-100 text-blue-600"><i class="fas fa-file-pdf w-4 mr-2 text-blue-600"></i> Laporan PDF</x-dropdown-link>@endif
                        </div>
                    </x-slot>
                </x-dropdown>
                @can('update', $project)
                    <a href="{{ route('projects.budget-items.index', $project) }}" class="inline-flex items-center bg-green-600 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-green-700 transform hover:scale-105 transition ease-in-out duration-150">
                        <i class="fas fa-wallet mr-2"></i> Anggaran
                    </a>
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center bg-amber-500 text-white font-bold text-sm px-4 py-2 rounded-lg shadow-md hover:bg-amber-600 transform hover:scale-105 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i> Edit Kegiatan
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>
    
    <div x-data="projectDetail()">
        <div class="py-12 bg-gray-50"> {{-- Pastikan latar belakang halaman konsisten --}}
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Ringkasan Kegiatan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"> {{-- Gap lebih besar --}}
                        <div class="bg-white p-6 rounded-xl shadow-xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-blue-500 hover:shadow-2xl"> {{-- Kartu KPI modern --}}
                            <div class="text-blue-600 mb-3 drop-shadow-md"><i class="fas fa-list-check fa-4x"></i></div>
                            <p class="text-5xl font-extrabold text-blue-700">{{ $stats['total'] }}</p>
                            <p class="text-sm text-gray-600 mt-2 font-semibold">Total Tugas</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-yellow-500 hover:shadow-2xl"> {{-- Kartu KPI modern --}}
                            <div class="text-yellow-500 mb-3 drop-shadow-md"><i class="fas fa-hourglass-start fa-4x"></i></div>
                            <p class="text-5xl font-extrabold text-yellow-600">{{ $stats['pending'] }}</p>
                            <p class="text-sm text-gray-600 mt-2 font-semibold">Tugas Menunggu</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-orange-500 hover:shadow-2xl"> {{-- Kartu KPI modern --}}
                            <div class="text-orange-500 mb-3 drop-shadow-md"><i class="fas fa-person-digging fa-4x"></i></div>
                            <p class="text-5xl font-extrabold text-orange-600">{{ $stats['in_progress'] }}</p>
                            <p class="text-sm text-gray-600 mt-2 font-semibold">Dikerjakan</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-xl text-center transform hover:-translate-y-1 transition-all duration-300 ease-in-out border-b-4 border-green-500 hover:shadow-2xl"> {{-- Kartu KPI modern --}}
                            <div class="text-green-500 mb-3 drop-shadow-md"><i class="fas fa-check-double fa-4x"></i></div>
                            <p class="text-5xl font-extrabold text-green-600">{{ $stats['completed'] }}</p>
                            <p class="text-sm text-gray-600 mt-2 font-semibold">Selesai</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-xl"> {{-- Shadow dan rounded-xl --}}
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'tasks'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'tasks', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tasks' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">Daftar Tugas</button>
                            <button @click="activeTab = 'info'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'info', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'info' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">Informasi & Aktivitas</button>
                            @can('update', $project)
                                <button @click="activeTab = 'add'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'add', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'add' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">Tambah Tugas Baru</button>
                            @endcan
                        </nav>
                    </div>
                    <div>
                        <div x-show="activeTab === 'tasks'" x-cloak>
                            <!-- Task Filter and Search Form -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg shadow-inner">
                                <form action="{{ route('projects.show', $project) }}" method="GET">
                                    <input type="hidden" name="tab" value="tasks">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <label for="task_search" class="sr-only">Cari Tugas</label>
                                            <input type="text" name="task_search" id="task_search" placeholder="Cari judul tugas..." value="{{ request('task_search') }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label for="task_status" class="sr-only">Status</label>
                                            <select name="task_status" id="task_status" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">Semua Status</option>
                                                <option value="pending" @selected(request('task_status') == 'pending')>Menunggu</option>
                                                <option value="in_progress" @selected(request('task_status') == 'in_progress')>Dikerjakan</option>
                                                <option value="for_review" @selected(request('task_status') == 'for_review')>Direview</option>
                                                <option value="completed" @selected(request('task_status') == 'completed')>Selesai</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="task_priority" class="sr-only">Prioritas</label>
                                            <select name="task_priority" id="task_priority" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">Semua Prioritas</option>
                                                @foreach(\App\Models\Task::PRIORITIES as $priority)
                                                    <option value="{{ $priority }}" @selected(request('task_priority') == $priority)>{{ ucfirst($priority) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="task_assignee" class="sr-only">Pelaksana</label>
                                            <select name="task_assignee" id="task_assignee" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">Semua Pelaksana</option>
                                                @foreach($projectMembers as $member)
                                                    <option value="{{ $member->id }}" @selected(request('task_assignee') == $member->id)>{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <a href="{{ route('projects.show', $project) }}?tab=tasks" class="px-4 py-2 bg-gray-600 text-white rounded-md text-xs hover:bg-gray-700">Reset</a>
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">Filter</button>
                                    </div>
                                </form>
                            </div>

                            <div class="space-y-4">
                                @forelse($tasks as $task)
                                    <x-task-card :task="$task"/>
                                @empty
                                    <div class="text-center py-8">
                                        <p class="text-gray-500">Tidak ada tugas yang cocok dengan kriteria Anda.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-6">
                                {{ $tasks->links() }}
                            </div>
                        </div>
                        <div x-show="activeTab === 'info'" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"> {{-- Container chart lebih menonjol --}}
                                        <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center"><i class="fas fa-chart-pie mr-2 text-indigo-600"></i> Distribusi Status Tugas</h3>
                                        <div class="h-64 flex items-center justify-center"> {{-- Menambahkan tinggi tetap untuk chart dan flex untuk centering --}}
                                            <canvas id="taskStatusChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"> {{-- Container tim proyek lebih menonjol --}}
                                        <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center"><i class="fas fa-people-group mr-2 text-teal-600"></i> Tim Kegiatan</h3>
                                        <ul class="space-y-2">
                                            <li class="flex items-center space-x-2 text-gray-700">
                                                <i class="fas fa-user-shield text-gray-500"></i> <span class="font-bold">Ketua Tim:</span><span>{{ optional($project->leader)->name ?? 'N/A' }}</span>
                                            </li>
                                        </ul>
                                        <h4 class="font-bold mt-4 mb-2 text-gray-800 flex items-center"><i class="fas fa-user-group mr-2 text-cyan-600"></i> Anggota:</h4>
                                        <ul class="list-disc list-inside mt-2 text-gray-700 space-y-1 ml-4"> {{-- List indent --}}
                                            @forelse($project->members as $member)
                                                <li class="flex items-center"><i class="fas fa-circle-dot text-gray-400 text-xs mr-2"></i> {{ $member->name }}</li>
                                            @empty
                                                <p class="text-sm text-gray-500">Tidak ada anggota tim tambahan.</p>
                                            @endforelse
                                        </ul>
                                    </div>

                                    {{-- --- AWAL PENAMBAHAN BAGIAN RIWAYAT PEMINJAMAN --- --}}
                                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"> {{-- Container riwayat peminjaman lebih menonjol --}}
                                        <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center"><i class="fas fa-handshake-angle mr-2 text-purple-600"></i> Riwayat Permintaan Peminjaman</h3>
                                        <div class="space-y-4">
                                            @forelse($loanRequests as $request)
                                                <div class="border-l-4 rounded-r-lg p-4 shadow-sm transition-all duration-200 ease-in-out 
                                                    @if($request->status == 'approved') border-green-500 bg-green-50 @elseif($request->status == 'rejected') border-red-500 bg-red-50 @else border-yellow-500 bg-yellow-50 @endif 
                                                    hover:shadow-md hover:scale-[1.01]"> {{-- Styling kartu permintaan peminjaman --}}
                                                    <p class="text-sm font-semibold text-gray-800 flex items-center">
                                                        <i class="fas fa-user-tag mr-2 text-gray-500"></i> Permintaan untuk <strong>{{ $request->requestedUser?->name ?? 'N/A' }}</strong>
                                                    </p>
                                                    <p class="text-xs text-gray-600 mt-1 flex items-center">
                                                        <i class="fas fa-user-plus mr-2 text-gray-400"></i> Oleh: {{ $request->requester?->name ?? 'N/A' }}
                                                        <span class="mx-2">|</span>
                                                        <i class="fas fa-clock mr-1 text-gray-400"></i> {{ $request->created_at->diffForHumans() }}
                                                    </p>
                                                    <div class="mt-3">
                                                        @if ($request->status == 'approved')
                                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-200 text-green-900 shadow-sm"><i class="fas fa-check-circle mr-1"></i> Disetujui oleh {{ $request->approver?->name ?? 'N/A' }}</span>
                                                        @elseif ($request->status == 'pending')
                                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-200 text-yellow-900 shadow-sm"><i class="fas fa-hourglass-half mr-1"></i> Menunggu persetujuan</span>
                                                        @elseif ($request->status == 'rejected')
                                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-200 text-red-900 shadow-sm"><i class="fas fa-times-circle mr-1"></i> Ditolak oleh {{ $request->approver?->name ?? 'N/A' }}</span>
                                                            @if($request->rejection_reason)
                                                                <p class="text-xs text-gray-700 mt-1 italic pl-1"><i class="fas fa-info-circle mr-1"></i> "{{ $request->rejection_reason }}"</p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500 p-4 text-center bg-gray-50 rounded-lg">Tidak ada riwayat permintaan peminjaman untuk kegiatan ini.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                    {{-- --- AKHIR PENAMBAHAN BAGIAN RIWAYAT PEMINJAMAN --- --}}

                                </div>
                                <div class="space-y-6">
                                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"> {{-- Container detail proyek lebih menonjol --}}
                                        <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center"><i class="fas fa-info-circle mr-2 text-blue-600"></i> Detail Kegiatan</h3>
                                        <p class="text-gray-700 text-sm leading-relaxed mb-4">{{ $project->description }}</p>
                                        <div class="text-sm grid grid-cols-2 gap-y-3 text-gray-600 border-t pt-4"> {{-- Grid untuk detail, border-top --}}
                                            <p class="font-medium flex items-center"><i class="fas fa-user-tie mr-2 text-gray-400"></i> Dibuat Oleh:</p><p class="font-semibold text-gray-800">{{ $project->owner->name ?? 'N/A' }}</p>
                                            <p class="font-medium flex items-center"><i class="fas fa-calendar-plus mr-2 text-gray-400"></i> Tanggal Mulai:</p><p class="font-semibold text-gray-800">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}</p>
                                            <p class="font-medium flex items-center"><i class="fas fa-calendar-check mr-2 text-gray-400"></i> Tanggal Selesai:</p><p class="font-semibold text-gray-800">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}</p>
                                            <p class="font-medium flex items-center"><i class="fas fa-building mr-2 text-gray-400"></i> Eselon 2:</p><p class="font-semibold text-gray-800">{{ optional($project->eselon2)->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300"> {{-- Container aktivitas terbaru lebih menonjol --}}
                                        <h3 class="text-lg font-bold mb-3 text-gray-800 flex items-center"><i class="fas fa-history mr-2 text-gray-600"></i> Aktivitas Terbaru</h3>
                                        <ul class="space-y-3">
                                            @forelse($project->activities->take(5) as $activity)
                                            <li class="text-sm text-gray-700 pb-2 border-b border-gray-100 last:border-b-0 flex items-start"> {{-- Styling list aktivitas --}}
                                                <i class="fas fa-dot-circle text-gray-400 text-xs mt-1 mr-3"></i>
                                                <div>
                                                    <span class="font-semibold text-gray-800">{{ optional($activity->user)->name ?? 'User Telah Dihapus' }}</span>
                                                    @switch($activity->description)
                                                        @case('created_project') membuat kegiatan ini @break
                                                        @case('updated_project') memperbarui kegiatan ini @break
                                                        @case('created_task') membuat tugas "<span class="font-medium italic">{{ optional($activity->subject)->title ?? '...' }}</span>" @break
                                                        @case('updated_task') memperbarui tugas "<span class="font-medium italic">{{ optional($activity->subject)->title ?? '...' }}</span>" @break
                                                        @case('deleted_task') menghapus sebuah tugas @break
                                                        @default melakukan sebuah aktivitas @endswitch
                                                    <span class="block text-xs text-gray-500 mt-0.5">{{ $activity->created_at->diffForHumans() }}</span>
                                                </div>
                                            </li>
                                            @empty
                                                <p class="text-sm text-gray-500 p-4 text-center bg-gray-50 rounded-lg">Tidak ada aktivitas terbaru.</p>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-show="activeTab === 'add'" x-cloak>
                            <form action="{{ route('tasks.store', $project) }}" method="POST">
                                <div class="space-y-6"> {{-- Spasi lebih besar --}}
                                    @csrf
                                    <div>
                                        <label for="add_title" class="block text-sm font-semibold text-gray-700 mb-1">Judul Tugas <span class="text-red-600">*</span></label>
                                        <input type="text" name="title" id="add_title" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('title') }}" required>
                                    </div>
                                    <div>
                                        <label for="add_description" class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Tugas (Opsional)</label>
                                        <textarea name="description" id="add_description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">{{ old('description') }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6"> {{-- Gap lebih besar --}}
                                        <div>
                                            <label for="add_deadline" class="block text-sm font-semibold text-gray-700 mb-1">Deadline <span class="text-red-600">*</span></label>
                                            <input type="date" name="deadline" id="add_deadline" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('deadline') }}" required>
                                        </div>
                                        <div>
                                            <label for="add_estimated_hours" class="block text-sm font-semibold text-gray-700 mb-1">Estimasi Jam <span class="text-red-600">*</span></label>
                                            <input type="number" step="0.5" name="estimated_hours" id="add_estimated_hours" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('estimated_hours') }}" placeholder="Contoh: 2.5" required>
                                        </div>
                                        <div>
                                            <label for="add_priority" class="block text-sm font-semibold text-gray-700 mb-1">Prioritas <span class="text-red-600">*</span></label>
                                            <select name="priority" id="add_priority" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                                                <option value="low">Rendah</option>
                                                <option value="medium" selected>Sedang</option>
                                                <option value="high">Tinggi</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="add_assignees" class="block text-sm font-semibold text-gray-700 mb-1">Tugaskan Kepada <span class="text-red-600">*</span></label>
                                        <select name="assignees[]" id="add_assignees" multiple class="block w-full" placeholder="Pilih pelaksana tugas">
                                            @foreach($projectMembers as $member)
                                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6"> {{-- Tombol submit: konsisten --}}
                                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                        <i class="fas fa-plus-circle mr-2"></i> Simpan Tugas
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Chart.js dan Tom Select --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script>
            function projectDetail() {
                return {
                    runningTaskGlobal: {{ optional(Auth::user()->timeLogs()->whereNull('end_time')->first())->task_id ?? 'null' }},
                    activeTab: 'tasks',
                    isChartInitialized: false,
                    isTomSelectInitialized: false,
                    init() {
                        console.log('Initializing projectDetail component...');

                        this.$watch('activeTab', value => {
                            console.log('activeTab changed to:', value);
                            if (value === 'info' && !this.isChartInitialized) {
                                this.$nextTick(() => this.initChart());
                            }
                            // Initialize TomSelect only when the 'add' tab is clicked for the first time.
                            if (value === 'add' && !this.isTomSelectInitialized) {
                                this.$nextTick(() => {
                                    this.initTomSelect();
                                    this.isTomSelectInitialized = true;
                                });
                            }
                        });

                        // Jika ada hash di URL, coba aktifkan tab yang sesuai
                        if (window.location.hash) {
                            const hash = window.location.hash.substring(1);
                            if (hash === 'info') {
                                this.activeTab = 'info';
                            } else if (hash === 'add') {
                                this.activeTab = 'add';
                            }
                        }
                    },
                    initTomSelect() {
                         console.log('Initializing TomSelect for #add_assignees');
                         new TomSelect('#add_assignees', {
                             plugins: ['remove_button'],
                             create: false,
                             placeholder: 'Pilih pelaksana tugas'
                         });
                    },
                    initChart() {
                        const ctx = document.getElementById('taskStatusChart');
                        if (ctx) {
                            // Hancurkan instance chart yang ada sebelum membuat yang baru (jika ada)
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }

                            const stats = @json($stats);
                            // Pastikan data chart tidak kosong
                            if (stats.total === 0 && stats.pending === 0 && stats.in_progress === 0 && stats.completed === 0) {
                                ctx.style.display = 'none'; // Sembunyikan canvas
                                const parentDiv = ctx.parentElement;
                                if (parentDiv && !parentDiv.querySelector('.chart-no-data-message')) {
                                    const noDataMessage = document.createElement('p');
                                    noDataMessage.className = 'chart-no-data-message text-center text-gray-500 py-4';
                                    noDataMessage.textContent = 'Tidak ada data tugas untuk menampilkan distribusi.';
                                    parentDiv.appendChild(noDataMessage);
                                }
                                return;
                            } else {
                                ctx.style.display = 'block'; // Pastikan canvas terlihat jika ada data
                                const noDataMessage = ctx.parentElement.querySelector('.chart-no-data-message');
                                if (noDataMessage) noDataMessage.remove(); // Hapus pesan jika ada data
                            }

                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Menunggu', 'Dikerjakan', 'Selesai'],
                                    datasets: [{
                                        data: [stats.pending, stats.in_progress, stats.completed],
                                        backgroundColor: ['#facc15', '#f97316', '#22c55e'], // yellow-500, orange-600, green-500
                                        hoverOffset: 8, // Efek hover sedikit lebih besar
                                        borderColor: '#ffffff', // Border putih
                                        borderWidth: 2 // Lebar border
                                    }]
                                },
                                options: { 
                                    responsive: true, 
                                    maintainAspectRatio: false, // Penting agar h-64 berfungsi
                                    plugins: { 
                                        legend: { 
                                            position: 'bottom',
                                            labels: {
                                                font: {
                                                    size: 14,
                                                    family: 'Figtree'
                                                },
                                                color: '#374151'
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    let label = context.label || '';
                                                    if (label) {
                                                        label += ': ';
                                                    }
                                                    if (context.parsed) {
                                                        label += context.parsed + ' Tugas'; // Menambahkan "Tugas"
                                                    }
                                                    return label;
                                                }
                                            }
                                        }
                                    } 
                                }
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
                            const data = await this.postData(`/tasks/${taskId}/time-log/start`);
                            this.runningTaskGlobal = data.running_task_id;
                            // Tidak perlu reload, Alpine akan reaktif
                        } catch (error) {
                            alert('Gagal memulai timer: ' + error.message);
                        }
                    },

                    async stopTimer(taskId) {
                        try {
                            const data = await this.postData(`/tasks/${taskId}/time-log/stop`);
                            this.runningTaskGlobal = null;
                            this.updateTaskTimeLogDisplay(taskId, data.time_log_summary);
                        } catch (error) {
                            alert('Gagal menghentikan timer: ' + error.message);
                        }
                    },

                    updateTaskTimeLogDisplay(taskId, summary) {
                        const displayDiv = document.getElementById(`time-log-display-${taskId}`);
                        if (displayDiv) {
                            displayDiv.innerHTML = `
                                <p>Waktu Estimasi: <span class="font-bold">${summary.estimated} jam</span></p>
                                <p>Waktu Tercatat: <span class="font-bold text-blue-600">${summary.logged}</span></p>
                            `;
                        }
                    },

                    async submitComment(event, taskId) {
                        const form = event.target;
                        const formData = new FormData(form);
                        const commentBody = formData.get('body').trim();

                        if (!commentBody) return;

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                            });

                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({ message: 'Terjadi kesalahan pada server.' }));
                                throw new Error(errorData.message);
                            }

                            const data = await response.json();

                            const commentList = document.getElementById(`comment-list-${taskId}`);
                            const noCommentMessage = document.getElementById(`no-comment-message-${taskId}`);
                            if (noCommentMessage) {
                                noCommentMessage.remove();
                            }
                            commentList.insertAdjacentHTML('beforeend', data.comment_html);
                            form.querySelector('input[name="body"]').value = '';

                        } catch (error) {
                            alert('Gagal mengirim komentar: ' + error.message);
                        }
                    },

                    async submitSubtask(event, taskId) {
                        const form = event.target;
                        const formData = new FormData(form);
                        const title = formData.get('title').trim();
                        if (!title) return;

                        try {
                            const response = await fetch(form.action, { // Corrected to use form.action
                                method: 'POST',
                                body: formData,
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                            });
                             if (!response.ok) throw new Error('Gagal menambahkan sub-tugas.');
                            const data = await response.json();

                            const subtaskList = document.getElementById(`subtask-list-${taskId}`);
                            const noSubtaskMessage = document.getElementById(`no-subtask-message-${taskId}`);
                            if (noSubtaskMessage) noSubtaskMessage.remove();

                            subtaskList.insertAdjacentHTML('beforeend', data.subtask_html);
                            form.querySelector('input[name="title"]').value = '';
                        } catch (error) {
                            alert(error.message);
                        }
                    },

                    async toggleSubtask(subtaskId, taskId) {
                        try {
                             const response = await fetch(`/subtasks/${subtaskId}`, {
                                method: 'PATCH',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                body: JSON.stringify({ is_completed: document.querySelector(`#subtask-${subtaskId} input[type=checkbox]`).checked })
                            });
                            if (!response.ok) throw new Error('Gagal memperbarui sub-tugas.');
                            const data = await response.json();
                            this.updateTaskProgress(taskId, data.task_progress, data.task_status);
                        } catch (error) {
                             alert(error.message);
                        }
                    },

                    async deleteSubtask(event, subtaskId) {
                        if (!confirm('Yakin ingin menghapus rincian tugas ini?')) return;

                        const form = event.target;
                        try {
                            const response = await fetch(`/subtasks/${subtaskId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error('Gagal menghapus sub-tugas.');
                            document.getElementById(`subtask-${subtaskId}`).remove();
                        } catch (error) {
                            alert(error.message);
                        }
                    },

                    updateTaskProgress(taskId, progress, status) {
                        const taskCard = document.getElementById(`task-${taskId}`);
                        if (taskCard) {
                            const progressBar = taskCard.querySelector('.progress-bar');
                            const progressText = taskCard.querySelector('.text-sm.font-medium.text-blue-700');
                            if (progressBar) progressBar.style.width = `${progress}%`;
                            if (progressText) progressText.textContent = `${progress}%`;

                            if (status) {
                                const statusBadge = taskCard.querySelector('.badge-status');
                                if (statusBadge) {
                                    statusBadge.textContent = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                                    // Update color classes
                                    statusBadge.className = 'badge-status text-xs font-semibold px-3 py-1 rounded-full'; // Reset classes
                                    if (status === 'completed') {
                                        statusBadge.classList.add('bg-green-100', 'text-green-800');
                                    } else if (status === 'in_progress') {
                                        statusBadge.classList.add('bg-blue-100', 'text-blue-800');
                                    } else if (status === 'for_review') {
                                        statusBadge.classList.add('bg-orange-100', 'text-orange-800');
                                    } else if (status === 'pending') {
                                        statusBadge.classList.add('bg-yellow-100', 'text-yellow-800');
                                    } else {
                                        statusBadge.classList.add('bg-gray-100', 'text-gray-800');
                                    }
                                }
                            }
                        }
                    },

                    async submitAttachment(event, taskId) {
                        const form = event.target;
                        const formData = new FormData(form);
                        if (!formData.get('file').name) return;

                        try {
                            const response = await fetch(`/tasks/${taskId}/attachments`, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                            });
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({ message: 'Gagal mengunggah file.' }));
                                throw new Error(errorData.message);
                            }
                            const data = await response.json();

                            const attachmentList = document.getElementById(`attachment-list-${taskId}`);
                            const noAttachmentMessage = document.getElementById(`no-attachment-message-${taskId}`);
                            if (noAttachmentMessage) noAttachmentMessage.remove();

                            attachmentList.insertAdjacentHTML('beforeend', data.attachment_html);
                            form.reset();
                            alert('File berhasil diunggah!');
                        } catch (error) {
                            alert('Gagal mengunggah file: ' + error.message);
                        }
                    },

                    async deleteAttachment(event, attachmentId) {
                        if (!confirm('Yakin ingin menghapus file ini?')) return;

                        try {
                             const response = await fetch(`/attachments/${attachmentId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error('Gagal menghapus file.');
                            document.getElementById(`attachment-${attachmentId}`).remove();
                        } catch (error) {
                            alert(error.message);
                        }
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>