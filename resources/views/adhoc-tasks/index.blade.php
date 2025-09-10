<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tugas Harian / Non-Kegiatan') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-secondary-button :href="route('adhoc-tasks.workflow')">
                    <i class="fas fa-sitemap mr-2"></i>
                    Lihat Alur Kerja
                </x-secondary-button>
                <a href="{{ route('adhoc-tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Tugas
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Filter and Search Form -->
                    <form action="{{ route('adhoc-tasks.index') }}" method="GET" class="mb-6 p-4 bg-gray-50 rounded-lg border">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">

                            {{-- Kolom Pencarian --}}
                            <div class="lg:col-span-5">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Tugas</label>
                                <input type="text" name="search" id="search" placeholder="Cari judul atau deskripsi..." value="{{ request('search') }}" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>

                            {{-- Kolom Status --}}
                            <div>
                                <label for="task_status_id" class="block text-sm font-medium text-gray-700 mb-1">Status Tugas</label>
                                <select name="task_status_id" id="task_status_id" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    <option value="">Semua Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" @selected(request('task_status_id') == $status->id)>{{ $status->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kolom Prioritas --}}
                            <div>
                                <label for="priority_level_id" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                                <select name="priority_level_id" id="priority_level_id" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    <option value="">Semua Prioritas</option>
                                    @foreach($priorityLevels as $priority)
                                        <option value="{{ $priority->id }}" @selected(request('priority_level_id') == $priority->id)>{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kolom Filter Personel --}}
                             @if(Auth::user()->canManageUsers())
                            <div class="lg:col-span-2">
                                <label for="personnel_id" class="block text-sm font-medium text-gray-700 mb-1">Filter Personel</label>
                                <select name="personnel_id" id="personnel_id" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    <option value="">Semua Personel</option>
                                    @foreach($subordinates as $subordinate)
                                        <option value="{{ $subordinate->id }}" @selected(request('personnel_id') == $subordinate->id)>{{ $subordinate->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                        </div>

                        {{-- Filter Tanggal --}}
                        <div class="mt-4 pt-4 border-t grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <div class="md:col-span-2">
                                <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Rentang Tanggal (Deadline)</label>
                                <input type="text" name="date_range" id="date_range" value="{{ request('date_range') }}" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="Pilih rentang tanggal...">
                            </div>
                        </div>


                        {{-- Tombol Aksi Form --}}
                        <div class="mt-6 pt-4 border-t flex items-center justify-between">
                            {{-- Tombol Cetak dipindah ke sini --}}
                            <a href="#" id="print-report-btn" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 shadow-sm">
                                <i class="fas fa-print mr-2"></i>
                                <span>Cetak Laporan</span>
                            </a>
                            <div class="flex items-center justify-end gap-4">
                                <div class="flex items-center gap-2">
                                    <label for="sort_by" class="text-sm font-medium text-gray-700">Urutkan:</label>
                                    <select name="sort_by" id="sort_by" class="rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                                        <option value="deadline" @selected(request('sort_by', 'deadline') == 'deadline')>Deadline</option>
                                        <option value="created_at" @selected(request('sort_by', 'created_at') == 'created_at')>Tanggal Dibuat</option>
                                    </select>
                                </div>
                                <a href="{{ route('adhoc-tasks.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm hover:bg-gray-700">Reset</a>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="space-y-6">
                        @forelse ($assignedTasks as $task)
                            <div class="block p-6 border border-gray-200 rounded-xl bg-white shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 ease-in-out">
                                <div class="flex justify-between items-start">
                                    <div class="flex-grow">
                                        <div class="flex items-center mb-2">
                                            <p class="font-bold text-xl text-indigo-700">{{ $task->title }}</p>
                                            @if($task->is_outside_office_hours)
                                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" title="Tugas ini ditandai sebagai pekerjaan di luar jam kerja normal.">
                                                    <i class="fas fa-moon mr-1.5"></i> Luar Jam Kerja
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600 space-x-3">
                                            <span class="inline-flex items-center"><i class="far fa-calendar-alt text-gray-400 mr-1"></i> Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                                            <span class="inline-flex items-center"><i class="far fa-clock text-gray-400 mr-1"></i> Estimasi: {{ $task->estimated_hours }} jam</span>
                                            <span class="inline-flex items-center"><i class="fas fa-users text-gray-400 mr-1"></i> Ditugaskan ke: 
                                                @foreach($task->assignees as $assignee)
                                                    <span class="font-medium text-gray-800">{{ $assignee->name }}{{ !$loop->last ? ',' : '' }}</span>
                                                @endforeach
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 flex-shrink-0">
                                        <a href="{{ route('adhoc-tasks.edit', $task) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600">
                                            <i class="fas fa-edit mr-1"></i> Detail/Edit
                                        </a>
                                    </div>
                                </div>
                                @if($task->description)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm text-gray-700">
                                            {{ Str::limit($task->description, 200) }}
                                        </p>
                                    </div>
                                @endif
                                <div class="mt-4">
                                    <div class="flex justify-between mb-2 items-center">
                                        <span class="text-base font-semibold text-blue-700">Progress</span>
                                        <span class="text-lg font-bold text-blue-700">{{ $task->progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-blue-600 h-3 rounded-full shadow-inner" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-gray-50 p-6 rounded-xl shadow-md text-center py-10">
                                <p class="text-gray-500 text-lg">Tidak ada tugas harian yang cocok dengan kriteria Anda.</p>
                                <a href="{{ route('adhoc-tasks.index') }}" class="mt-4 text-sm text-indigo-600 hover:underline">Hapus Filter</a>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        {{ $assignedTasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const printBtn = document.getElementById('print-report-btn');
                const form = document.querySelector('form');

                function updatePrintLink() {
                    const baseUrl = "{{ route('adhoc-tasks.print-report') }}";
                    const currentQueryString = window.location.search;
                    printBtn.href = baseUrl + currentQueryString;
                }

                form.addEventListener('input', updatePrintLink);
                form.addEventListener('change', updatePrintLink);
                updatePrintLink();

                flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d M Y",
                });
            });
        </script>
    @endpush
</x-app-layout>