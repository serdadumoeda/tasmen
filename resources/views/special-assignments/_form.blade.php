<div class="space-y-4">
    <div>
        <label for="title" class="block font-medium text-sm text-gray-700">Judul / Uraian Penugasan</label>
        <input type="text" name="title" id="title" value="{{ old('title', $assignment->title ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('title') border-red-500 @enderror" required>
        @error('title') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="sk_number" class="block font-medium text-sm text-gray-700">Nomor SK (Opsional)</label>
        <input type="text" name="sk_number" id="sk_number" value="{{ old('sk_number', $assignment->sk_number ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('sk_number') border-red-500 @enderror">
        @error('sk_number') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="start_date" class="block font-medium text-sm text-gray-700">Tanggal Mulai</label>
            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', isset($assignment->start_date) ? $assignment->start_date->format('Y-m-d') : '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('start_date') border-red-500 @enderror" required>
            @error('start_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="end_date" class="block font-medium text-sm text-gray-700">Tanggal Selesai (Opsional)</label>
            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', isset($assignment->end_date) ? $assignment->end_date->format('Y-m-d') : '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('end_date') border-red-500 @enderror">
            @error('end_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>
    </div>
    
    <div>
        <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
        <select name="status" id="status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('status') border-red-500 @enderror" required>
            <option value="AKTIF" @selected(old('status', $assignment->status ?? 'AKTIF') == 'AKTIF')>Aktif</option>
            <option value="SELESAI" @selected(old('status', $assignment->status ?? '') == 'SELESAI')>Selesai</option>
        </select>
        @error('status') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi (Opsional)</label>
        <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description', $assignment->description ?? '') }}</textarea>
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('special-assignments.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
        {{ $assignment->exists ? 'Update SK' : 'Simpan SK' }}
    </button>
</div>