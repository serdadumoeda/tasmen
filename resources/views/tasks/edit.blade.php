<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Tugas: {{ $task->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Blok Persetujuan untuk Pimpinan (jika ada) --}}
                    @if ($task->project && $task->status === 'pending_review' && Gate::allows('approve', $task))
                        {{-- ... (Kode persetujuan tidak berubah) ... --}}
                    @endif

                    <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <fieldset @if($task->status === 'pending_review' && !auth()->user()->can('approve', $task)) disabled @endif>
                            
                            @if ($errors->any())
                                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                                </div>
                            @endif

                            {{-- Judul --}}
                            <div class="mb-4">
                                <label for="title" class="block font-medium text-sm text-gray-700">Judul Tugas</label>
                                <input type="text" name="title" id="title" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" value="{{ old('title', $task->title) }}" required>
                            </div>
                            
                            {{-- Deskripsi --}}
                            <div class="mb-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi</label>
                                <textarea name="description" id="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $task->description) }}</textarea>
                            </div>

                            <div class="mb-4 relative z-20">
                                <label for="assignees" class="block font-medium text-sm text-gray-700">Ditugaskan Kepada</label>
                                @if (auth()->user()->can('update', $task))
                                    <select name="assignees[]" id="assignees" class="block mt-1 w-full tom-select-multiple" multiple required>
                                        @foreach ($projectMembers as $member)
                                            <option value="{{ $member->id }}" @selected(in_array($member->id, old('assignees', $task->assignees->pluck('id')->toArray())))>
                                                {{ $member->name }} ({{ $member->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md">
                                        @foreach($task->assignees as $assignee)
                                            {{ $assignee->name }} ({{ $assignee->role }}){{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </div>
                                    @foreach($task->assignees as $assignee)
                                        <input type="hidden" name="assignees[]" value="{{ $assignee->id }}">
                                    @endforeach
                                @endif
                            </div>
                            
                            {{-- ... (Sisa form seperti Deadline, Estimasi, Prioritas, Progress tidak berubah) ... --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="deadline" class="block font-medium text-sm text-gray-700">Deadline</label>
                                    <input type="date" name="deadline" id="deadline" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" value="{{ old('deadline', \Carbon\Carbon::parse($task->deadline)->format('Y-m-d')) }}" required>
                                </div>
                                <div>
                                    <label for="estimated_hours" class="block font-medium text-sm text-gray-700">Estimasi Jam</label>
                                    <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="priority" class="block text-sm font-medium text-gray-700">Prioritas</label>
                                <select name="priority" id="priority" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="low" @selected(old('priority', $task->priority ?? 'medium') == 'low')>Rendah</option>
                                    <option value="medium" @selected(old('priority', $task->priority ?? 'medium') == 'medium')>Sedang</option>
                                    <option value="high" @selected(old('priority', $task->priority ?? 'medium') == 'high')>Tinggi</option>
                                </select>
                            </div>
                            
                            <div class="mt-4">
                                <label for="progress" class="block font-medium text-sm text-gray-700">Progress: <span id="progress-value">{{ old('progress', $task->progress) }}</span>%</label>
                                <input type="range" name="progress" id="progress" min="0" max="100" class="block mt-1 w-full" value="{{ old('progress', $task->progress) }}" oninput="document.getElementById('progress-value').innerText = this.value">
                            </div>

                            {{-- Unggah Lampiran Baru --}}
                            <div class="mt-4">
                                <label for="file_upload" class="block text-sm font-medium text-gray-700">Unggah Lampiran Baru</label>
                                <input type="file" name="file_upload" id="file_upload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                <p class="text-xs text-gray-500 mt-1">Maksimal ukuran: 5MB.</p>
                            </div>
                        </fieldset>

                        {{-- Tombol Simpan --}}
                        @if($task->status !== 'pending_review' || auth()->user()->can('approve', $task))
                            <div class="flex items-center justify-between mt-8 pt-4 border-t">
                                @if ($task->project_id)
                                    <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali ke Proyek</a>
                                @else
                                    <a href="{{ route('adhoc-tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali ke Daftar Tugas Harian</a>
                                @endif
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Simpan Perubahan</button>
                            </div>
                        @endif
                    </form>
                    
                    <div class="mt-8 pt-6 border-t">
                        <h3 class="font-semibold text-lg text-gray-800">Daftar Lampiran</h3>
                        <ul class="mt-4 space-y-2">
                            @forelse ($task->attachments as $attachment)
                                <li class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded-md group">
                                    <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-indigo-600 hover:underline truncate">
                                        {{ $attachment->filename }}
                                    </a>
                                    
                                    {{-- Tombol Hapus Lampiran --}}
                                    @can('update', $task)
                                        <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file ini? Aksi ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                                &times; Hapus
                                            </button>
                                        </form>
                                    @endcan
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">Belum ada lampiran.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>