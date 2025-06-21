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
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                            </div>
                        @endif

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
                            {{-- Ubah 'name', tambahkan 'multiple', dan perbarui logika @selected --}}
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

                        <div class="flex items-center justify-between mt-6">
                            <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                &larr; Kembali ke Proyek
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>