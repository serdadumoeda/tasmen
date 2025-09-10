@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert"> {{-- Menambahkan rounded-lg dan shadow-md --}}
        <ul class="list-disc list-inside">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
@endif

<div class="space-y-6"> {{-- Mengubah space-y-4 menjadi space-y-6 untuk konsistensi dengan tampilan kartu --}}
    <div>
        <label for="title" class="block font-semibold text-sm text-gray-700 mb-1">Judul Tugas <span class="text-red-500">*</span></label> {{-- Menambahkan font-semibold dan mb-1 --}}
        <input type="text" name="title" id="title" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('title', $task->title ?? '') }}" required> {{-- Mengubah rounded-md menjadi rounded-lg, menambahkan fokus, dan transisi --}}
    </div>
    
    <div>
        <label for="description" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi (Opsional)</label> {{-- Menambahkan font-semibold dan mb-1 --}}
        <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">{{ old('description', $task->description ?? '') }}</textarea> {{-- Mengubah rounded-md menjadi rounded-lg, menambahkan fokus, dan transisi --}}
    </div>

    @if (Auth::user()->canManageUsers())
        <div class="relative z-20">
            <label for="assignees" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-users-line mr-2 text-gray-500"></i> Ditugaskan Kepada <span class="text-red-500">*</span>
            </label>
            <select name="assignees[]" id="assignees" class="block mt-1 w-full tom-select-multiple" multiple required placeholder="Pilih Anggota Tim...">
                @foreach ($assignableUsers as $member)
                    <option value="{{ $member->id }}" @selected(in_array($member->id, old('assignees', (isset($task) && $task->assignees) ? $task->assignees->pluck('id')->all() : [])))>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Anda bisa memilih lebih dari satu orang.</p>
        </div>
    @else
        <input type="hidden" name="assignees[]" value="{{ Auth::id() }}">
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="start_date" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" name="start_date" id="start_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('start_date', optional($task->start_date)->format('Y-m-d')) }}">
        </div>
        <div>
            <label for="deadline" class="block font-semibold text-sm text-gray-700 mb-1">Deadline <span class="text-red-500">*</span></label>
            <input type="date" name="deadline" id="deadline" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('deadline', optional($task->deadline)->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="estimated_hours" class="block font-semibold text-sm text-gray-700 mb-1">Estimasi Jam <span class="text-red-500">*</span></label>
            <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('estimated_hours', $task->estimated_hours ?? '') }}" placeholder="Contoh: 2.5" required>
        </div>
    </div>
    
    <div>
        <label for="priority_level_id" class="block font-semibold text-sm text-gray-700 mb-1">Prioritas</label>
        {{-- MODIFIKASI: Tambahkan placeholder --}}
        <select name="priority_level_id" id="priority_level_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
            <option value="">-- Pilih Prioritas --</option>
            @php
                // Find the default 'medium' priority ID if it exists, otherwise fallback to null.
                $defaultPriorityId = $priorities->firstWhere('name', 'Medium')?->id;
            @endphp
            @foreach($priorities as $priority)
                {{-- MODIFIKASI: Ganti $priority->label menjadi $priority->name --}}
                <option value="{{ $priority->id }}" @selected(old('priority_level_id', $task->priority_level_id ?? $defaultPriorityId) == $priority->id)>
                    {{ $priority->name }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div>
        <label for="file_upload" class="block font-semibold text-sm text-gray-700 mb-1">Lampiran Baru (Opsional)</label>
        <input type="file" name="file_upload" id="file_upload" class="block w-full mt-1 text-sm text-gray-500
            file:mr-4 file:py-2 file:px-4
            file:rounded-full file:border-0
            file:text-sm file:font-semibold
            file:bg-blue-50 file:text-blue-700
            hover:file:bg-blue-100 hover:file:shadow-sm
            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
        @error('file_upload') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
        @if(isset($task) && $task->attachments->isNotEmpty())
            <p class="text-xs text-gray-600 mt-2">Lampiran saat ini:</p>
            <ul class="mt-1 list-disc list-inside">
            @foreach($task->attachments as $attachment)
                <li class="text-sm"><a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-indigo-600 hover:underline">{{ $attachment->filename }}</a></li>
            @endforeach
            </ul>
        @endif
    </div>
</div>