@props(['task'])

@php
    $priorityBorderColor = match($task->priority) {
        'high' => 'border-l-red-500',
        'medium' => 'border-l-yellow-500',
        'low' => 'border-l-green-500',
        default => 'border-l-gray-300',
    };
    $isOverdue = $task->deadline && $task->deadline < now() && $task->progress < 100;
@endphp

{{-- Desain Kartu Kanban yang Interaktif --}}
<div class="bg-white rounded-lg shadow-md border-l-4 {{ $priorityBorderColor }} overflow-hidden transition-all hover:shadow-xl">
    <div class="p-4">
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-bold text-gray-800 pr-2">{{ $task->title }}</h4>
            <a href="{{ route('projects.show', $task->project_id) }}#task-{{ $task->id }}" class="text-gray-400 hover:text-blue-600 flex-shrink-0" title="Lihat Detail Lengkap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
        </div>

        <div class="mb-3">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-semibold text-gray-500">Progress</span>
                <span id="progress-text-{{ $task->id }}" class="text-sm font-bold text-blue-600">{{ $task->progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar-{{ $task->id }}" class="bg-blue-600 h-2 rounded-full progress-bar" style="width: {{ $task->progress }}%"></div>
            </div>
        </div>
        
        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
            <div class="flex -space-x-2">
                @foreach($task->assignees->take(3) as $assignee)
                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://ui-avatars.com/api/?name={{ urlencode($assignee->name) }}&background=random&color=fff&font-size=0.5" alt="{{ $assignee->name }}" title="{{ $assignee->name }}">
                @endforeach
            </div>

            @if($task->subTasks->isNotEmpty())
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="text-xs font-semibold text-gray-500 hover:text-indigo-600 flex items-center">
                        <span id="subtask-counter-{{ $task->id }}">
                            Rincian ({{ $task->subTasks->where('is_completed', true)->count() }}/{{ $task->subTasks->count() }})
                        </span>
                        <svg class="h-4 w-4 ml-1 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-transition class="absolute z-10 mt-2 w-72 bg-white rounded-lg shadow-xl p-4 border" x-cloak>
                        <div class="space-y-2">
                            @foreach($task->subTasks as $subTask)
                                <label for="subtask-check-{{ $subTask->id }}" class="flex items-center text-sm text-gray-700 hover:bg-gray-50 p-1 rounded cursor-pointer">
                                    <input type="checkbox" id="subtask-check-{{ $subTask->id }}" 
                                           data-subtask-id="{{ $subTask->id }}"
                                           class="subtask-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                           @if($subTask->is_completed) checked @endif>
                                    <span class="ml-3 {{ $subTask->is_completed ? 'line-through text-gray-500' : '' }}">{{ $subTask->title }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>