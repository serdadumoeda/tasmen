@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <label for="title" class="block font-medium text-sm text-gray-700">Judul Tugas <span class="text-red-500">*</span></label>
        <input type="text" name="title" id="title" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" value="{{ old('title') }}" required>
    </div>
    
    <div>
        <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi (Opsional)</label>
        <textarea name="description" id="description" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description') }}</textarea>
    </div>

    @if (Auth::user()->canManageUsers())
        <div>
            <label for="assignees" class="block font-medium text-sm text-gray-700">Tugaskan Kepada <span class="text-red-500">*</span></label>
            <select name="assignees[]" id="assignees" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" multiple required>
                @foreach ($assignableUsers as $member)
                    <option value="{{ $member->id }}" @selected(in_array($member->id, old('assignees', [])))>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Anda bisa memilih lebih dari satu orang dengan menahan tombol Ctrl/Cmd.</p>
        </div>
    @else
        <input type="hidden" name="assignees[]" value="{{ Auth::id() }}">
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="deadline" class="block font-medium text-sm text-gray-700">Deadline <span class="text-red-500">*</span></label>
            <input type="date" name="deadline" id="deadline" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" value="{{ old('deadline') }}" required>
        </div>
        <div>
            <label for="estimated_hours" class="block font-medium text-sm text-gray-700">Estimasi Jam <span class="text-red-500">*</span></label>
            <input type="number" step="0.5" name="estimated_hours" id="estimated_hours" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" value="{{ old('estimated_hours') }}" placeholder="Contoh: 2.5" required>
        </div>
    </div>
    
    {{-- BARU: Menambahkan field Status dan Progress --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
            <select name="status" id="status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                <option value="pending" @selected(old('status') == 'pending')>Pending</option>
                <option value="in_progress" @selected(old('status') == 'in_progress')>In Progress</option>
                <option value="completed" @selected(old('status') == 'completed')>Completed</option>
            </select>
        </div>
        <div>
            <label for="progress" class="block font-medium text-sm text-gray-700">Progress: <span id="progress-value">{{ old('progress', 0) }}</span>%</label>
            <input type="range" name="progress" id="progress" min="0" max="100" class="block mt-1 w-full" value="{{ old('progress', 0) }}" oninput="document.getElementById('progress-value').innerText = this.value">
        </div>
    </div>
    
    {{-- BARU: Menambahkan field untuk upload lampiran --}}
    <div>
        <label for="file_upload" class="block font-medium text-sm text-gray-700">Lampiran (Opsional)</label>
        <input type="file" name="file_upload" id="file_upload" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        @error('file_upload') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
    </div>
</div>