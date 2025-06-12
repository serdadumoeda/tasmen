@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Oops! Ada yang salah:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label for="name" class="block font-medium text-sm text-gray-700">Nama Proyek</label>
    <input type="text" name="name" id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" value="{{ old('name', $project->name ?? '') }}" required>
</div>

<div class="mb-4">
    <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi</label>
    <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>{{ old('description', $project->description ?? '') }}</textarea>
</div>

<div class="mb-4">
    <label for="leader_id" class="block font-medium text-sm text-gray-700">Ketua Tim</label>
    <select name="leader_id" id="leader_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        <option value="">-- Pilih Ketua Tim --</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected(old('leader_id', $project->leader_id ?? '') == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label for="members" class="block font-medium text-sm text-gray-700">Anggota Tim (pilih beberapa dengan Ctrl/Cmd + Klik)</label>
    <select name="members[]" id="members" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 h-40" multiple required>
        @php
            $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id') : []));
        @endphp
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected($projectMemberIds->contains($user->id))>{{ $user->name }}</option>
        @endforeach
    </select>
</div>