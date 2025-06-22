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
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <div>
        <label for="start_date" class="block font-medium text-sm text-gray-700">Tanggal Mulai</label>
        <input type="date" name="start_date" id="start_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" value="{{ old('start_date', isset($project->start_date) ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}">
    </div>
    <div>
        <label for="end_date" class="block font-medium text-sm text-gray-700">Tanggal Selesai</label>
        <input type="date" name="end_date" id="end_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" value="{{ old('end_date', isset($project->end_date) ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '') }}">
    </div>
</div>
{{-- PERBAIKAN 1: Gunakan $potentialMembers, bukan $users --}}
<div class="mb-4">
    <label for="leader_id" class="block font-medium text-sm text-gray-700">Pimpinan Proyek</label>
    <select name="leader_id" id="leader_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        <option value="">-- Pilih Pimpinan Proyek --</option>
        @foreach ($potentialMembers as $member)
            <option value="{{ $member->id }}" @selected(old('leader_id', $project->leader_id ?? '') == $member->id)>
                {{ $member->name }} ({{ $member->role }})
            </option>
        @endforeach
    </select>
</div>

{{-- PERBAIKAN 2: Gunakan $potentialMembers, bukan $users --}}
<div class="mb-4">
    <label for="members" class="block font-medium text-sm text-gray-700">Anggota Tim</label>
    {{-- MODIFIKASI: tambahkan class 'tom-select-multiple' --}}
    <select name="members[]" id="members" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 h-40 tom-select-multiple" multiple required>
        @php
            $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id') : []));
        @endphp
        @foreach ($potentialMembers as $member)
            <option value="{{ $member->id }}" @selected($projectMemberIds->contains($member->id))>
                {{ $member->name }} ({{ $member->role }})
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">Anda bisa mengetik nama untuk mencari dan menekan backspace untuk menghapus.</p>
</div>