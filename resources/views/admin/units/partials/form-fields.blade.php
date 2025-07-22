<div class="grid grid-cols-1 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Unit</label>
        <input type="text" name="name" id="name" value="{{ old('name', $unit->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
    </div>

    <div>
        <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
        <select name="level" id="level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @foreach(['Eselon I', 'Eselon II', 'Koordinator', 'Sub Koordinator'] as $level)
                <option value="{{ $level }}" @if(old('level', $unit->level ?? '') == $level) selected @endif>{{ $level }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="parent_unit_id" class="block text-sm font-medium text-gray-700">Unit Atasan</label>
        <select name="parent_unit_id" id="parent_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">-- Tidak ada --</option>
            @foreach($units as $parent)
                <option value="{{ $parent->id }}" @if(old('parent_unit_id', $unit->parent_unit_id ?? '') == $parent->id) selected @endif>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
</div>
