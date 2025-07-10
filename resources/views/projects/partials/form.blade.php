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

{{-- Bagian form yang sudah ada --}}
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

{{-- ====================================================================== --}}
{{-- PERBAIKAN DIMULAI DI SINI --}}
{{-- ====================================================================== --}}
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
    
    {{-- "Wadah" untuk menampilkan info beban kerja --}}
    <div id="leaderWorkloadInfo" class="mt-2 text-sm">
        {{-- Konten akan diisi oleh JavaScript saat pimpinan dipilih --}}
    </div>
</div>
{{-- ====================================================================== --}}
{{-- PERBAIKAN SELESAI --}}
{{-- ====================================================================== --}}



{{-- Bagian Anggota Tim --}}
<div class="mb-4">
    <div class="flex justify-between items-center mb-1">
        <label for="members" class="block font-medium text-sm text-gray-700">Anggota Tim</label>
        <button type="button" id="showResourcePoolBtn" class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-md hover:bg-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Pilih dari Tim Terbuka
        </button>
    </div>

    <select name="members[]" id="members" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" multiple required>
        @php
            $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id') : []));
        @endphp
        @foreach ($potentialMembers as $member)
            <option value="{{ $member->id }}" @selected($projectMemberIds->contains($member->id))>
                {{ $member->name }} ({{ $member->role }})
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">Tahan tombol Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu anggota.</p>
    
    
    <div id="membersWorkloadInfo" class="mt-2 text-sm">
        {{-- Konten akan diisi oleh JavaScript saat anggota dipilih --}}
    </div>
</div>


{{-- Modal (pop-up) untuk Tim Terbuka --}}
<div id="resourcePoolModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Pilih Anggota dari Tim Terbuka</p>
            <button type="button" id="closeModalBtn" class="cursor-pointer z-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="mt-4">
            <div class="overflow-y-auto" style="max-height: 50vh;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">Pilih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody id="resourcePoolMembers" class="bg-white divide-y divide-gray-200">
                        <tr><td colspan="4" class="text-center p-4">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex justify-end pt-4 border-t mt-4">
            <button type="button" id="addMembersFromPoolBtn" class="px-4 py-2 bg-gray-800 text-white text-base font-medium rounded-md hover:bg-gray-700">
                Tambahkan Anggota Terpilih
            </button>
        </div>
    </div>
</div>