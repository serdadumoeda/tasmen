<div class="flex items-center justify-between" id="subtask-{{ $subTask->id }}">
    <div class="flex items-center">
        <input type="checkbox"
               name="is_completed"
               class="h-4 w-4 rounded border-gray-300"
               @change="toggleSubtask({{ $subTask->id }}, {{ $subTask->task_id }})"
               @if($subTask->is_completed) checked @endif>
        <label class="ml-3 text-sm {{ $subTask->is_completed ? 'line-through text-gray-500' : 'text-gray-800' }}">
            {{ $subTask->title }}
        </label>
    </div>
    <form @submit.prevent="deleteSubtask($event, {{ $subTask->id }})">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-red-400 hover:text-red-600">&times;</button>
    </form>
</div>
