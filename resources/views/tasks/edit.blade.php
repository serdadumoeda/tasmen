<x-app-layout>
    <x-slot name="styles">
        <style>
            /* Style kustom untuk Tom Select (dari project.blade.php) */
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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Tugas: ') }} <span class="font-bold text-indigo-600">{{ $task->title }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    
                    {{-- Blok Persetujuan untuk Pimpinan (jika ada) --}}
                    @if ($task->project && $task->status === 'pending_review' && Gate::allows('approve', $task))
                        <div class="mb-6 p-5 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-lg shadow-md flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-4 mt-1"></i>
                            <div>
                                <h4 class="font-bold text-lg mb-1">Tugas Menunggu Persetujuan</h4>
                                <p class="text-sm">Tugas ini telah diselesaikan oleh pelaksana dan menunggu persetujuan Anda untuk ditandai sebagai 'Selesai'.</p>
                                <form action="{{ route('tasks.approve', $task) }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                        <i class="fas fa-check-circle mr-2"></i> Setujui & Selesaikan
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @php
                        $updateRoute = $task->project_id ? route('tasks.update', $task) : route('adhoc-tasks.update', $task);
                    @endphp
                    <form action="{{ $updateRoute }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <fieldset @if($task->status === 'pending_review' && !auth()->user()->can('approve', $task)) disabled @endif>
                            
                            @if ($errors->any())
                                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <i class="fas fa-exclamation-triangle h-5 w-5 text-red-500"></i>
                                        </div>
                                        <div class="ml-3">
                                            <strong class="font-bold">Oops! Ada yang salah:</strong>
                                            <ul class="mt-1.5 list-disc list-inside text-sm">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Judul --}}
                            <div class="mb-6"> {{-- Spasi lebih besar --}}
                                <label for="title" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-heading mr-2 text-gray-500"></i> Judul Tugas <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" id="title" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('title', $task->title) }}" required>
                            </div>
                            
                            {{-- Deskripsi --}}
                            <div class="mb-6"> {{-- Spasi lebih besar --}}
                                <label for="description" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-align-left mr-2 text-gray-500"></i> Deskripsi (Opsional)
                                </label>
                                <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">{{ old('description', $task->description) }}</textarea>
                            </div>

                            <div class="mb-6 relative z-20"> {{-- Spasi lebih besar --}}
                                <label for="assignees" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-users-line mr-2 text-gray-500"></i> Ditugaskan Kepada <span class="text-red-500">*</span>
                                </label>
                                @if (auth()->user()->can('update', $task))
                                    <select name="assignees[]" id="assignees" class="block mt-1 w-full tom-select-multiple" multiple required>
                                        @foreach ($projectMembers as $member)
                                            <option value="{{ $member->id }}" @selected(in_array($member->id, old('assignees', $task->assignees->pluck('id')->toArray())))>
                                                {{ $member->name }} ({{ $member->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="mt-1 p-3 bg-gray-100 border border-gray-300 rounded-lg shadow-sm text-gray-800 font-medium"> {{-- Styling div non-editable --}}
                                        <i class="fas fa-user-check mr-2 text-gray-500"></i>
                                        @foreach($task->assignees as $assignee)
                                            {{ $assignee->name }} ({{ $assignee->role }}){{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </div>
                                    @foreach($task->assignees as $assignee)
                                        <input type="hidden" name="assignees[]" value="{{ $assignee->id }}">
                                    @endforeach
                                @endif
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"> {{-- Gap dan margin lebih besar --}}
                                <div>
                                    <label for="deadline" class="block font-semibold text-sm text-gray-700 mb-1">
                                        <i class="fas fa-calendar-alt mr-2 text-gray-500"></i> Deadline <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="deadline" id="deadline" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('deadline', \Carbon\Carbon::parse($task->deadline)->format('Y-m-d')) }}" required>
                                </div>
                                <div>
                                    <label for="estimated_hours" class="block font-semibold text-sm text-gray-700 mb-1">
                                        <i class="fas fa-hourglass-half mr-2 text-gray-500"></i> Estimasi Jam (Opsional)
                                    </label>
                                    <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('estimated_hours', $task->estimated_hours) }}" placeholder="Contoh: 2.5">
                                </div>
                            </div>
                            
                            <div class="mb-6"> {{-- Spasi lebih besar --}}
                                <label for="priority" class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="fas fa-flag mr-2 text-gray-500"></i> Prioritas <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" id="priority" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
                                    <option value="low" @selected(old('priority', $task->priority ?? 'medium') == 'low')>Rendah</option>
                                    <option value="medium" @selected(old('priority', $task->priority ?? 'medium') == 'medium')>Sedang</option>
                                    <option value="high" @selected(old('priority', $task->priority ?? 'medium') == 'high')>Tinggi</option>
                                </select>
                            </div>
                            
                            <div class="mb-6"> {{-- Spasi lebih besar --}}
                                <label for="progress" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-spinner mr-2 text-gray-500"></i> Progress: <span id="progress-value">{{ old('progress', $task->progress) }}</span>%
                                </label>
                                <input type="range" name="progress" id="progress" min="0" max="100" class="block mt-1 w-full h-2 rounded-full appearance-none bg-gray-200 cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:shadow-md [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-indigo-600 [&::-moz-range-thumb]:shadow-md" value="{{ old('progress', $task->progress) }}" oninput="document.getElementById('progress-value').innerText = this.value">
                            </div>

                            {{-- Unggah Lampiran Baru --}}
                            <div class="mb-6"> {{-- Spasi lebih besar --}}
                                <label for="file_upload" class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="fas fa-paperclip mr-2 text-gray-500"></i> Unggah Lampiran Baru (Opsional)
                                </label>
                                <input type="file" name="file_upload" id="file_upload" class="block w-full text-sm text-gray-500 
                                    file:mr-4 file:py-2 file:px-4 
                                    file:rounded-full file:border-0 
                                    file:text-sm file:font-semibold 
                                    file:bg-blue-50 file:text-blue-700 
                                    hover:file:bg-blue-100 hover:file:shadow-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                                <p class="text-xs text-gray-500 mt-1">Maksimal ukuran: 5MB (PDF, JPG, PNG, DOC, DOCX, XLS, XLSX).</p>
                            </div>
                        </fieldset>

                        {{-- Tombol Simpan --}}
                        @if($task->status !== 'pending_review' || auth()->user()->can('approve', $task))
                            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200"> {{-- Border dan padding atas --}}
                                @if ($task->project_id)
                                    <a href="{{ route('projects.show', $task->project) }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200">
                                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Kegiatan
                                    </a>
                                @else
                                    <a href="{{ route('adhoc-tasks.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200">
                                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Tugas Harian
                                    </a>
                                @endif
                                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        @endif
                    </form>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200"> {{-- Border dan padding atas --}}
                        <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-file-lines mr-2 text-blue-600"></i> Daftar Lampiran
                        </h3>
                        <ul class="mt-4 space-y-3"> {{-- Spasi lebih besar --}}
                            @forelse ($task->attachments as $attachment)
                                <li class="flex justify-between items-center text-sm p-3 bg-gray-50 rounded-lg shadow-sm group hover:bg-gray-100 transition-colors duration-150"> {{-- Styling list item lampiran --}}
                                    <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-indigo-600 hover:underline truncate flex items-center font-medium">
                                        <i class="fas fa-file-alt mr-2 text-gray-500"></i> {{ $attachment->filename }}
                                    </a>
                                    
                                    {{-- Tombol Hapus Lampiran --}}
                                    @can('update', $task)
                                        <form action="{{ route('attachments.destroy', $attachment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file ini? Aksi ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200" title="Hapus Lampiran">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </li>
                            @empty
                                <li class="text-sm text-gray-500 p-4 text-center bg-gray-50 rounded-lg shadow-inner">
                                    <i class="fas fa-box-open fa-2x text-gray-400 mb-2"></i>
                                    <p>Belum ada lampiran.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi TomSelect untuk elemen dengan kelas 'tom-select-multiple'
            const selectElement = document.getElementById('assignees');
            if (selectElement && selectElement.classList.contains('tom-select-multiple')) {
                new TomSelect(selectElement, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: null,
                    placeholder: 'Pilih Anggota Tim',
                    // Pastikan nilai awal dipilih dengan benar
                    items: @json(old('assignees', $task->assignees->pluck('id')->toArray()))
                });
            }

            // Script untuk progress bar
            const progressInput = document.getElementById('progress');
            const progressValueSpan = document.getElementById('progress-value');
            if (progressInput && progressValueSpan) {
                progressInput.addEventListener('input', function() {
                    progressValueSpan.innerText = this.value;
                });
            }
        });
    </script>
    @endpush
</x-app-layout>