<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">Kegiatan: {{ $project->name }}</a> /
                    <span class="font-bold">{{ __('Dashboard Tim') }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Ringkasan kinerja tim dalam kegiatan.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-8"
             x-data="{
                openMemberId: null,
                memberTasks: [],
                isLoading: false,
                toggleMemberTasks(memberId) {
                    if (this.openMemberId === memberId) {
                        this.openMemberId = null; // Close if already open
                        return;
                    }
                    this.isLoading = true;
                    this.openMemberId = memberId;
                    fetch(`/projects/{{ $project->id }}/team/${memberId}/tasks`)
                        .then(response => response.json())
                        .then(data => {
                            this.memberTasks = data;
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching tasks:', error);
                            this.isLoading = false;
                            this.openMemberId = null; // Close on error
                        });
                }
             }">

            {{-- Ringkasan per Anggota --}}
            <div class="space-y-6">
                @forelse ($teamSummary as $summary)
                <div class="bg-white p-6 rounded-xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 ease-in-out">
                    {{-- Wrapper div for click handler --}}
                    <div class="cursor-pointer" @click="toggleMemberTasks({{ $summary['member_id'] }})">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                            {{-- User Info --}}
                            <div class="flex items-center mb-4 md:mb-0">
                                <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-xl font-bold mr-4 shadow-inner">
                                    {{ strtoupper(substr($summary['member_name'], 0, 1)) }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">{{ $summary['member_name'] }}</h3>
                                <i class="fas ml-4 text-gray-400 transition-transform" :class="{ 'fa-chevron-down': openMemberId !== {{ $summary['member_id'] }}, 'fa-chevron-up': openMemberId === {{ $summary['member_id'] }} }"></i>
                            </div>
                            {{-- Priority Badges --}}
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-semibold inline-flex items-center px-2.5 py-1 rounded-full bg-purple-100 text-purple-800" title="Kritis">
                                    <i class="fas fa-bomb mr-1.5"></i> {{ $summary['priority_counts']->get('critical', 0) }}
                                </span>
                                <span class="text-xs font-semibold inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 text-red-800" title="Tinggi">
                                    <i class="fas fa-fire mr-1.5"></i> {{ $summary['priority_counts']->get('high', 0) }}
                                </span>
                                <span class="text-xs font-semibold inline-flex items-center px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800" title="Sedang">
                                    <i class="fas fa-grip-lines mr-1.5"></i> {{ $summary['priority_counts']->get('medium', 0) }}
                                </span>
                                <span class="text-xs font-semibold inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-800" title="Rendah">
                                    <i class="fas fa-leaf mr-1.5"></i> {{ $summary['priority_counts']->get('low', 0) }}
                                </span>
                            </div>
                        </div>

                        {{-- Workload --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Beban Kerja (Jam)</label>
                            <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
                                @php $workloadPercent = ($summary['total_estimated_hours'] > 0) ? ($summary['total_logged_hours'] / $summary['total_estimated_hours']) * 100 : 0; @endphp
                                <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-4 rounded-full" style="width: {{ min($workloadPercent, 100) }}%"></div>
                            </div>
                            <div class="text-xs text-right text-gray-600 mt-1">{{ $summary['total_logged_hours'] }} / {{ $summary['total_estimated_hours'] }} jam</div>
                        </div>

                        {{-- Weighted Progress --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Progress Tertimbang</label>
                            <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full" style="width: {{ $summary['weighted_average_progress'] }}%"></div>
                            </div>
                            <div class="text-xs text-right text-gray-600 mt-1">{{ $summary['weighted_average_progress'] }}% Selesai</div>
                        </div>

                        {{-- Task Stats --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center border-t pt-4">
                            <div class="p-2">
                                <p class="text-2xl font-bold text-gray-800">{{ $summary['total_tasks'] }}</p>
                                <p class="text-sm text-gray-500">Total Tugas</p>
                            </div>
                            <div class="p-2">
                                <p class="text-2xl font-bold text-blue-600">{{ $summary['inprogress_tasks'] }}</p>
                                <p class="text-sm text-gray-500">Dikerjakan</p>
                            </div>
                            <div class="p-2">
                                <p class="text-2xl font-bold text-green-600">{{ $summary['completed_tasks'] }}</p>
                                <p class="text-sm text-gray-500">Selesai</p>
                            </div>
                            <div class="p-2">
                                <p class="text-2xl font-bold text-red-600 flex items-center justify-center">
                                    {{ $summary['overdue_tasks'] }}
                                    @if($summary['overdue_tasks'] > 0)
                                        <i class="fas fa-triangle-exclamation ml-2 text-red-500 animate-pulse" title="Ada tugas yang lewat tenggat!"></i>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">Overdue</p>
                            </div>
                        </div>
                    </div>

                    {{-- Collapsible Task List --}}
                    <div x-show="openMemberId === {{ $summary['member_id'] }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" class="mt-6 border-t pt-4">
                        {{-- Loading State --}}
                        <div x-show="isLoading" class="text-center p-4">
                            <i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i>
                            <p class="mt-2 text-gray-600">Memuat tugas...</p>
                        </div>

                        {{-- Task List --}}
                        <div x-show="!isLoading && memberTasks.length > 0">
                            <h4 class="text-lg font-semibold mb-3 text-gray-700">Daftar Tugas:</h4>
                            <ul class="space-y-3">
                                <template x-for="task in memberTasks" :key="task.id">
                                    <li class="p-3 bg-gray-50 rounded-lg shadow-sm flex justify-between items-start">
                                        <div>
                                            <a :href="`/projects/{{$project->id}}#task-${task.id}`" class="font-semibold text-indigo-600 hover:underline" x-text="task.title"></a>
                                            <p class="text-sm text-gray-500" x-text="`Tenggat: ${new Date(task.deadline).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`"></p>
                                            <div x-show="task.sub_tasks && task.sub_tasks.length > 0" class="mt-2 pl-4">
                                                <p class="text-xs font-semibold text-gray-600">Sub-Tugas:</p>
                                                <ul class="list-disc list-inside text-sm text-gray-500">
                                                    <template x-for="subtask in task.sub_tasks" :key="subtask.id">
                                                       <li :class="{ 'line-through': subtask.is_completed }" x-text="subtask.title"></li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold inline-flex px-2 py-1 rounded-full"
                                                  :class="{
                                                      'bg-yellow-100 text-yellow-800': task.status === 'pending',
                                                      'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                                      'bg-green-100 text-green-800': task.status === 'completed',
                                                      'bg-gray-100 text-gray-800': task.status !== 'pending' && task.status !== 'in_progress' && task.status !== 'completed'
                                                  }"
                                                  x-text="task.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())">
                                            </span>
                                            <div class="mt-1 text-xs text-gray-400" x-text="`Progress: ${task.progress}%`"></div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                         {{-- Empty State --}}
                         <div x-show="!isLoading && memberTasks.length === 0">
                            <p class="text-center text-gray-500 p-4">Tidak ada tugas yang ditugaskan untuk anggota ini dalam kegiatan ini.</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white p-8 rounded-xl shadow-xl text-center">
                    <p class="text-gray-500 text-lg">Tidak ada anggota tim dalam kegiatan ini.</p>
                    <p class="text-gray-400 text-sm mt-2">Pastikan anggota tim telah ditambahkan pada detail kegiatan.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>