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
    <div id="leaderWorkloadInfo" class="mt-2 text-sm"></div>
</div>

<div class="mb-4">
    <div class="flex justify-between items-center mb-1">
        <label for="members" class="block font-medium text-sm text-gray-700">Anggota Tim</label>
        <button type="button" id="showMemberModalBtn" class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-md hover:bg-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Anggota
        </button>
    </div>

    <select name="members[]" id="members" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" multiple required>
        @php
            $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id')->all() : []));
        @endphp
        @foreach ($potentialMembers as $member)
            {{-- --- AWAL PERBAIKAN LOGIKA TAMPILAN --- --}}
            @php
                // Siapkan nama tampilan default
                $displayName = "{$member->name} ({$member->role})";

                // Cek apakah anggota ini BUKAN dari hierarki kita (berarti anggota pinjaman)
                $isExternal = isset($subordinateIds) && !$subordinateIds->contains($member->id);
                $request = isset($loanRequests) ? $loanRequests->get($member->id) : null;

                // Jika dia anggota pinjaman dan ada data permintaannya, tambahkan status sebagai teks
                if ($isExternal && $request) {
                    $statusText = '';
                    if ($request->status == 'approved') {
                        $statusText = ' [Disetujui]';
                    } elseif ($request->status == 'pending') {
                        $statusText = ' [Menunggu]';
                    } elseif ($request->status == 'rejected') {
                        $statusText = ' [Ditolak]';
                    }
                    $displayName .= $statusText;
                }
            @endphp

            <option value="{{ $member->id }}" @selected($projectMemberIds->contains($member->id))>
                {{-- Tampilkan nama yang sudah dimodifikasi dengan status --}}
                {{ $displayName }}
            </option>
            {{-- --- AKHIR PERBAIKAN LOGIKA TAMPILAN --- --}}
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">Tahan tombol Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu anggota.</p>
    <div id="membersWorkloadInfo" class="mt-2 text-sm"></div>
</div>