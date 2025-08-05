<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-building mr-2 text-gray-500"></i> Nama Unit <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $unit->name ?? '') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
        @error('name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="level" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-stairs mr-2 text-gray-500"></i> Level <span class="text-red-500">*</span>
        </label>
        <select name="level" id="level" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
            <option value="">-- Pilih Level --</option>
            @foreach(\App\Models\Unit::LEVELS as $levelData)
                <option value="{{ $levelData['name'] }}" @selected(old('level', $unit->level ?? '') == $levelData['name'])>{{ $levelData['name'] }}</option>
            @endforeach
        </select>
        @error('level') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="parent_unit_id" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-building-circle-arrow-up mr-2 text-gray-500"></i> Unit Atasan (Opsional)
        </label>
        <select name="parent_unit_id" id="parent_unit_id" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
            <option value="">-- Tidak ada --</option>
            @foreach($units as $parent)
                @if(isset($unit) && $unit->id == $parent->id) @continue @endif
                <option value="{{ $parent->id }}" @if(old('parent_unit_id', $unit->parent_unit_id ?? '') == $parent->id) selected @endif>{{ $parent->name }}</option>
            @endforeach
        </select>
        @error('parent_unit_id') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    @if(!isset($unit)) {{-- Only show on create form --}}
    <div class="md:col-span-2">
        <label for="main_jabatan_name" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-id-badge mr-2 text-gray-500"></i> Nama Jabatan Pimpinan Unit <span class="text-red-500">*</span>
        </label>
        <input type="text" name="main_jabatan_name" id="main_jabatan_name" value="{{ old('main_jabatan_name') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required placeholder="e.g., Kepala Divisi, Koordinator, etc.">
        <p class="text-xs text-gray-500 mt-1">Jabatan ini akan menjadi posisi pimpinan untuk unit yang baru dibuat.</p>
        @error('main_jabatan_name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>
    @endif
</div>