@props(['task']) {{-- MENGUBAH 'item' menjadi 'task' --}}

@php
    // Variabel $item tidak lagi ada, gunakan $task
    $isProject = $task instanceof App\Models\Project;
    $isTask = $task instanceof App\Models\Task;

    // Perhatikan bahwa di konteks kanban-card ini, $task akan selalu berupa instance App\Models\Task.
    // Logic untuk $isProject mungkin tidak akan pernah terpicu jika komponen ini hanya digunakan untuk tugas.
    // Namun, kita pertahankan struktur ini sesuai dengan penggunaan sebelumnya.

    if ($isProject) {
        $title = $task->name;
        $url = route('projects.show', $task);
        $progress = $task->progress;
        $assignees = $task->members; // Asumsikan 'members' adalah relasi di model Project
        $statusColorClass = $task->status_color_class;
    } elseif ($isTask) {
        $title = $task->title;
        // Pastikan $task->project adalah instance model Project yang dimuat (eager loading di controller)
        // atau relasi proyek akan di-load secara lazy di sini.
        $url = route('projects.show', $task->project) . '#task-' . $task->id; 
        $progress = $task->progress;
        $assignees = $task->assignees;
        $statusColorClass = match($task->priority) {
            'high' => 'border-l-red-500',
            'medium' => 'border-l-yellow-500',
            'low' => 'border-l-green-500',
            default => 'border-l-gray-300',
        };
    }
@endphp

<div class="bg-white rounded-lg shadow-md border-l-4 {{ $statusColorClass }} overflow-hidden transition-all hover:shadow-xl">
    <a href="{{ $url }}" class="block p-4">
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-bold text-gray-800 pr-2">{{ $title }}</h4>
            <span class="text-gray-400 hover:text-blue-600 flex-shrink-0" title="Lihat Detail Lengkap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </span>
        </div>

        @if($isProject)
            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($task->description, 100) }}</p> {{-- MENGUBAH $item->description --}}
        @endif

        <div class="mb-3">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-semibold text-gray-500">Progress</span>
                <span class="text-sm font-bold text-blue-600">{{ $progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
        </div>
        
        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
            <div class="flex -space-x-2">
                @foreach($assignees->take(5) as $assignee)
                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://ui-avatars.com/api/?name={{ urlencode($assignee->name) }}&background=random&color=fff&font-size=0.5" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
                @endforeach
            </div>

            @if($isTask && $task->subTasks->isNotEmpty()) {{-- MENGUBAH $item->subTasks --}}
                <div class="text-xs font-semibold text-gray-500">
                    Rincian ({{ $task->subTasks->where('is_completed', true)->count() }}/{{ $task->subTasks->count() }}) {{-- MENGUBAH $item->subTasks --}}
                </div>
            @elseif($isProject)
                <div class="text-xs font-semibold text-gray-500">
                    {{ $task->tasks_count }} Tugas {{-- MENGUBAH $item->tasks_count --}}
                </div>
            @endif
        </div>
    </a>
</div>