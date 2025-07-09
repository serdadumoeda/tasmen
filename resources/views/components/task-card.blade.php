@props(['task'])

@php
    $isOverdue = $task->deadline && $task->deadline < now() && $task->progress < 100;
@endphp

<div class="border border-gray-200 p-4 rounded-lg @if($isOverdue) border-red-300 bg-red-50 @endif" id="task-{{ $task->id }}">
    {{-- Header Kartu: Judul dan Tombol Aksi --}}
    <div class="flex justify-between items-start">
        <div>
            <h4 class="font-bold text-lg text-gray-800">{{ $task->title }}</h4>
            <p class="text-sm text-gray-600">
                Untuk: <strong>@foreach($task->assignees as $assignee){{ $assignee->name }}{{ !$loop->last ? ', ' : '' }}@endforeach</strong> 
                | Deadline: 
                <span class="@if($isOverdue) text-red-700 font-bold @endif">
                    {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : 'N/A' }}
                </span>
            </p>
        </div>
        <div class="flex items-center space-x-2 flex-shrink-0">
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="inline-block px-3 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full hover:bg-amber-200 transition-colors">Edit</a>
            @endcan
            @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-block px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full hover:bg-red-200 transition-colors">Hapus</button>
                </form>
            @endcan
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="mt-2">
        <div class="flex justify-between mb-1 items-center">
            <span class="text-base font-medium text-blue-700">Progress</span>
            <div>
                @if($task->pending_review)
                    <span class="px-2 py-1 text-xs font-semibold text-orange-800 bg-orange-200 rounded-full">Menunggu Review</span>
                @endif
                <span class="text-sm font-medium text-blue-700 ml-2">{{ $task->progress }}%</span>
            </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: {{ $task->progress }}%"></div>
        </div>
    </div>
    
    {{-- Tombol Aksi Persetujuan --}}
    <div class="mt-4 flex justify-end">
        @can('approve', $task)
            @if($task->pending_review)
                <form action="{{ route('tasks.approve', $task) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-lg hover:bg-green-600 shadow">Setujui & Selesaikan</button>
                </form>
            @endif
        @endcan
    </div>

    {{-- Detail Tambahan (Sub-tugas, Waktu, Lampiran, Komentar) dalam Accordion --}}
    <div x-data="{ open: false }" class="mt-4 border-t border-gray-200 pt-2">
        <button @click="open = !open" class="flex justify-between items-center w-full text-sm font-semibold text-gray-600 hover:text-gray-900">
            <span>Tampilkan Detail</span>
            <svg class="h-5 w-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>

        <div x-show="open" x-transition class="mt-4 space-y-4">
            {{-- Rincian Tugas (Subtask) --}}
            <div class="border-t pt-4"><h5 class="font-semibold text-sm mb-2 text-gray-700">Rincian Tugas</h5><div class="space-y-2">@forelse($task->subTasks as $subTask)<div class="flex items-center justify-between"><form action="{{ route('subtasks.update', $subTask) }}" method="POST" class="flex items-center">@csrf @method('PATCH')<input type="checkbox" name="is_completed" class="h-4 w-4 rounded border-gray-300" onchange="this.form.submit()" @if($subTask->is_completed) checked @endif><label class="ml-3 text-sm {{ $subTask->is_completed ? 'line-through text-gray-500' : 'text-gray-800' }}">{{ $subTask->title }}</label></form><form action="{{ route('subtasks.destroy', $subTask) }}" method="POST" onsubmit="return confirm('Hapus rincian tugas ini?');">@csrf @method('DELETE')<button type="submit" class="text-xs text-red-400 hover:text-red-600">&times;</button></form></div>@empty<p class="text-xs text-gray-500">Belum ada rincian tugas.</p>@endforelse</div><form action="{{ route('subtasks.store', $task) }}" method="POST" class="mt-3 flex space-x-2">@csrf<input type="text" name="title" class="flex-grow block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Tambah rincian baru..." required><button type="submit" class="px-3 py-1 bg-gray-700 text-white text-xs font-bold rounded hover:bg-gray-800">Tambah</button></form></div>
            {{-- Pencatatan Waktu --}}
            <div class="border-t pt-4" x-data="{ showManualForm: false }"><h5 class="font-semibold text-sm mb-2 text-gray-700">Pencatatan Waktu</h5><div class="flex justify-between items-center text-sm"><div id="time-log-display-{{ $task->id }}">@php $totalMinutes = $task->timeLogs->sum('duration_in_minutes'); $hours = floor($totalMinutes / 60); $minutes = $totalMinutes % 60; @endphp<p>Waktu Estimasi: <span class="font-bold">{{ (float)$task->estimated_hours ?? 0 }} jam</span></p><p>Waktu Tercatat: <span class="font-bold text-blue-600">{{ $hours }} jam {{ $minutes }} menit</span></p></div><div class="flex items-center space-x-2"><template x-if="runningTaskGlobal !== {{ $task->id }}"><button @click="startTimer({{ $task->id }})" class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded hover:bg-green-600" :disabled="runningTaskGlobal !== null">START</button></template><template x-if="runningTaskGlobal === {{ $task->id }}"><button @click="stopTimer({{ $task->id }})" class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded hover:bg-red-600 animate-pulse">STOP</button></template><button @click="showManualForm = !showManualForm" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded hover:bg-gray-300">MANUAL</button></div></div><div x-show="showManualForm" x-transition class="mt-4 border-t pt-4"><form action="{{ route('timelogs.storeManual', $task) }}" method="POST" class="flex items-end space-x-2">@csrf<div><label for="duration_in_minutes_{{ $task->id }}" class="block text-xs text-gray-600">Menit</label><input type="number" id="duration_in_minutes_{{ $task->id }}" name="duration_in_minutes" class="text-sm rounded-md border-gray-300 shadow-sm" style="width: 80px;" required></div><div><label for="log_date_{{ $task->id }}" class="block text-xs text-gray-600">Tanggal</label><input type="date" id="log_date_{{ $task->id }}" name="log_date" value="{{ now()->format('Y-m-d') }}" class="text-sm rounded-md border-gray-300 shadow-sm" required></div><button type="submit" class="h-9 px-3 bg-blue-600 text-white text-xs font-bold rounded hover:bg-blue-700">Simpan</button></form></div></div>
            {{-- Lampiran --}}
            <div class="border-t pt-4"><h5 class="font-semibold text-sm mb-2 text-gray-700">Lampiran</h5><ul class="list-disc list-inside space-y-1 mb-3">@forelse($task->attachments as $attachment)<li class="text-sm flex justify-between items-center"><a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $attachment->filename }}</a><form action="{{ route('attachments.destroy', $attachment) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="text-xs text-red-500 hover:text-red-700">&times;</button></form></li>@empty<li class="text-sm text-gray-500 list-none">Belum ada lampiran.</li>@endforelse</ul><form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data">@csrf<div class="flex items-center space-x-2"><input type="file" name="file" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required><button type="submit" class="px-3 py-1 bg-gray-700 text-white text-xs font-bold rounded hover:bg-gray-600">Unggah</button></div></form></div>
            {{-- Komentar --}}
            <div class="border-t pt-4"><h5 class="font-semibold text-sm mb-2 text-gray-700">Diskusi</h5><div class="space-y-3 mb-4">@forelse($task->comments as $comment)<div class="flex items-start space-x-2 text-sm"><span class="font-bold text-gray-800">{{ optional($comment->user)->name ?? 'User Dihapus' }}:</span><p class="text-gray-700">{{ $comment->body }}</p></div>@empty<p class="text-sm text-gray-500">Belum ada komentar.</p>@endforelse</div><form action="{{ route('tasks.comments.store', $task) }}" method="POST">@csrf<div class="flex space-x-2"><input type="text" name="body" class="flex-grow w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Tulis komentar..." required><button type="submit" class="px-3 py-1 bg-gray-700 text-white text-xs font-bold rounded hover:bg-gray-600">Kirim</button></div></form></div>
        </div>
    </div>
</div>