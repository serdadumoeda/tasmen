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
                    
                    {{-- ====================================================================== --}}
                    {{-- BLOK PERSETUJUAN UNTUK PIMPINAN (APPROVER) --}}
                    {{-- ====================================================================== --}}
                    @can('approve', $task)
                        @if ($task->status === 'pending_review')
                            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400">
                                <h3 class="font-bold text-yellow-800">Tugas ini Menunggu Persetujuan Anda</h3>
                                <p class="text-sm text-yellow-700 mt-1">Tugas ini telah ditandai selesai oleh anggota tim. Silakan review dan berikan persetujuan atau kembalikan tugas.</p>
                                
                                <div class="mt-4 flex flex-col sm:flex-row sm:space-x-3 space-y-2 sm:space-y-0">
                                    {{-- Tombol Setuju --}}
                                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        {{-- Kirim semua data asli agar tidak hilang saat update --}}
                                        <input type="hidden" name="title" value="{{ $task->title }}">
                                        <input type="hidden" name="description" value="{{ $task->description }}">
                                        @foreach($task->assignees as $assignee)
                                        <input type="hidden" name="assignees[]" value="{{ $assignee->id }}">
                                        @endforeach
                                        <input type="hidden" name="deadline" value="{{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') }}">
                                        <input type="hidden" name="estimated_hours" value="{{ $task->estimated_hours }}">
                                        
                                        {{-- Data yang diubah untuk persetujuan --}}
                                        <input type="hidden" name="status" value="completed">
                                        <input type="hidden" name="progress" value="100">
                                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Setuju</button>
                                    </form>
                                    
                                    {{-- Tombol Tolak --}}
                                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        {{-- Kirim semua data asli --}}
                                        <input type="hidden" name="title" value="{{ $task->title }}">
                                        <input type="hidden" name="description" value="{{ $task->description }}">
                                        @foreach($task->assignees as $assignee)
                                        <input type="hidden" name="assignees[]" value="{{ $assignee->id }}">
                                        @endforeach
                                        <input type="hidden" name="deadline" value="{{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') }}">
                                        <input type="hidden" name="estimated_hours" value="{{ $task->estimated_hours }}">

                                        {{-- Data yang diubah untuk penolakan --}}
                                        <input type="hidden" name="status" value="in_progress">
                                        <input type="hidden" name="progress" value="90"> {{-- Progress di-set ke 90% saat ditolak --}}
                                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Tolak (Kembalikan Tugas)</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endcan
                    {{-- ====================================================================== --}}
                    {{-- AKHIR BLOK PERSETUJUAN --}}
                    {{-- ====================================================================== --}}

                    {{-- Form utama untuk mengupdate detail tugas --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Fieldset ini akan menonaktifkan form jika tugas sedang direview oleh pengguna yang tidak berhak menyetujui --}}
                        <fieldset @if($task->status === 'pending_review' && !auth()->user()->can('approve', $task)) disabled @endif>
                            
                            @if ($errors->any())
                                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="title" class="block font-medium text-sm text-gray-700">Judul Tugas</label>
                                <input type="text" name="title" id="title" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" value="{{ old('title', $task->title) }}" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi</label>
                                <textarea name="description" id="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $task->description) }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label for="assignees" class="block font-medium text-sm text-gray-700">Ditugaskan Kepada (bisa pilih lebih dari satu)</label>
                                <select name="assignees[]" id="assignees" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" multiple required>
                                    @foreach ($projectMembers as $member)
                                        <option value="{{ $member->id }}" @selected(in_array($member->id, old('assignees', $task->assignees->pluck('id')->toArray())))>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="deadline" class="block font-medium text-sm text-gray-700">Deadline</label>
                                <input type="date" name="deadline" id="deadline" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" value="{{ old('deadline', \Carbon\Carbon::parse($task->deadline)->format('Y-m-d')) }}" required>
                            </div>

                            <div class="mb-4">
                                <label for="estimated_hours" class="block font-medium text-sm text-gray-700">Estimasi Jam</label>
                                <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                            </div>

                            <div class="mb-4">
                                <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
                                <select name="status" id="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="pending" @selected(old('status', $task->status) == 'pending')>Pending</option>
                                    <option value="in_progress" @selected(old('status', $task->status) == 'in_progress')>In Progress</option>
                                    <option value="pending_review" @selected(old('status', $task->status) == 'pending_review')>Pending Review</option>
                                    <option value="completed" @selected(old('status', $task->status) == 'completed')>Completed</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="progress" class="block font-medium text-sm text-gray-700">Progress: <span id="progress-value">{{ old('progress', $task->progress) }}</span>%</label>
                                <input type="range" name="progress" id="progress" min="0" max="100" class="block mt-1 w-full" value="{{ old('progress', $task->progress) }}" oninput="document.getElementById('progress-value').innerText = this.value">
                            </div>
                        </fieldset>

                        {{-- Tombol simpan hanya muncul jika form tidak dinonaktifkan --}}
                        @if($task->status !== 'pending_review' || auth()->user()->can('approve', $task))
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
                        @endif
                    </form>
                    
                    {{-- Bagian Lampiran --}}
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-base font-medium text-gray-800 mb-3">Lampiran</h3>
                        <ul class="list-disc list-inside space-y-1 mb-4">
                            @forelse($task->attachments as $attachment)
                                <li class="text-sm flex justify-between items-center group">
                                    <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:underline">
                                        {{ $attachment->filename }} 
                                        @if(property_exists($attachment, 'size') && $attachment->size)
                                        <span class="text-gray-500 text-xs">({{ \Illuminate\Support\Number::fileSize($attachment->size, precision: 2) }})</span>
                                        @endif
                                    </a>
                                    @can('update', $task)
                                    <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity">&times; Hapus</button>
                                    </form>
                                    @endcan
                                </li>
                            @empty
                                <li class="text-sm text-gray-500 list-none">Belum ada lampiran.</li>
                            @endforelse
                        </ul>
                        
                        @can('update', $task)
                        <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <label for="file_upload" class="block font-medium text-sm text-gray-700">Unggah File Baru</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="file" name="file" id="file_upload" class="text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">Unggah</button>
                            </div>
                             @error('file') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>