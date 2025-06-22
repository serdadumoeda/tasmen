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
                    
                    {{-- Form untuk mengupdate detail utama tugas --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                            </div>
                        @endif

                        {{-- Semua field untuk detail tugas --}}
                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700">Judul Tugas</label>
                            <input type="text" name="title" id="title" class="block mt-1 w-full" value="{{ old('title', $task->title) }}" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi</label>
                            <textarea name="description" id="description" rows="3" class="block mt-1 w-full">{{ old('description', $task->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="assignees" class="block font-medium text-sm text-gray-700">Ditugaskan Kepada (bisa pilih lebih dari satu)</label>
                            <select name="assignees[]" id="assignees" class="block mt-1 w-full" multiple required>
                                @foreach ($projectMembers as $member)
                                    <option value="{{ $member->id }}" @selected(in_array($member->id, old('assignees', $task->assignees->pluck('id')->toArray())))>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="deadline" class="block font-medium text-sm text-gray-700">Deadline</label>
                            <input type="date" name="deadline" id="deadline" class="block mt-1 w-full" value="{{ old('deadline', \Carbon\Carbon::parse($task->deadline)->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="estimated_hours" class="block font-medium text-sm text-gray-700">Estimasi Jam</label>
                            <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="block mt-1 w-full" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
                             <select name="status" id="status" class="block mt-1 w-full" required>
                                <option value="pending" @selected(old('status', $task->status) == 'pending')>Pending</option>
                                <option value="in_progress" @selected(old('status', $task->status) == 'in_progress')>In Progress</option>
                                <option value="completed" @selected(old('status', $task->status) == 'completed')>Completed</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="progress" class="block font-medium text-sm text-gray-700">Progress: <span id="progress-value">{{ old('progress', $task->progress) }}</span>%</label>
                            <input type="range" name="progress" id="progress" min="0" max="100" class="block mt-1 w-full" value="{{ old('progress', $task->progress) }}" oninput="document.getElementById('progress-value').innerText = this.value">
                        </div>

                        {{-- Tombol simpan dan kembali untuk form utama --}}
                        <div class="flex items-center justify-between mt-8 pt-4 border-t">
                             @if ($task->project_id)
                                <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    &larr; Kembali ke Proyek
                                </a>
                            @else
                                <a href="{{ route('adhoc-tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    &larr; Kembali ke Daftar Tugas Harian
                                </a>
                            @endif
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                    {{-- AKHIR DARI FORM UTAMA --}}

                    {{-- BAGIAN LAMPIRAN DENGAN FORM-NYA SENDIRI --}}
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-base font-medium text-gray-800 mb-3">Lampiran</h3>
                        <ul class="list-disc list-inside space-y-1 mb-4">
                            @forelse($task->attachments as $attachment)
                                <li class="text-sm flex justify-between items-center group">
                                    <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:underline">
                                        {{ $attachment->filename }} 
                                        @if(property_exists($attachment, 'size'))
                                        <span class="text-gray-500 text-xs">({{ \Illuminate\Support\Number::fileSize($attachment->size, precision: 2) }})</span>
                                        @endif
                                    </a>
                                    <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity">&times; Hapus</button>
                                    </form>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500 list-none">Belum ada lampiran.</li>
                            @endforelse
                        </ul>
                        
                        {{-- Form untuk upload file baru --}}
                        <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <label for="file_upload" class="block font-medium text-sm text-gray-700">Unggah File Baru</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="file" name="file" id="file_upload" class="text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">Unggah</button>
                            </div>
                             @error('file') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>