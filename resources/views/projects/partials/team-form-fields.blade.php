<div class="space-y-6"> {{-- Container for consistent spacing between form groups --}}
    <div>
        <label for="leader_id" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-user-tie mr-2 text-gray-500"></i> Pimpinan Kegiatan <span class="text-red-500">*</span>
        </label>
        <select name="leader_id" id="leader_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
            <option value="">-- Pilih Pimpinan Kegiatan --</option>
            @foreach ($potentialMembers as $member)
                <option value="{{ $member->id }}" @selected(old('leader_id', $project->leader_id ?? '') == $member->id)>
                    {{ $member->name }} ({{ $member->role }})
                </option>
            @endforeach
        </select>
        <div id="leaderWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div> {{-- Styling for workload info div --}}
    </div>

    <div>
        <div class="flex justify-between items-center mb-2"> {{-- Consistent spacing and alignment for label and button --}}
            <label for="members" class="block font-semibold text-sm text-gray-700">
                <i class="fas fa-users-line mr-2 text-gray-500"></i> Anggota Tim <span class="text-red-500">*</span>
            </label>
            <button type="button" id="showMemberModalBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-user-plus mr-2"></i> Tambah Anggota
            </button>
        </div>

        <select name="members[]" id="members" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 tom-select" multiple required> {{-- Added tom-select class for JS initialization --}}
            @php
                $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id')->all() : []));
            @endphp
            @foreach ($potentialMembers as $member)
                @php
                    // Prepare default display name
                    $displayName = "{$member->name} ({$member->role})";

                    // Check if member is external (borrowed) and if there's a loan request associated
                    $isExternal = isset($subordinateIds) && !$subordinateIds->contains($member->id); // Assuming $subordinateIds is available and is a collection of IDs
                    $request = isset($loanRequests) && $loanRequests->has($member->id) ? $loanRequests->get($member->id) : null;

                    // If it's a borrowed member and there's request data, append status as text
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
                    {{ $displayName }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Tahan tombol Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu anggota.</p>
        <div id="membersWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div> {{-- Styling for workload info div --}}
    </div>
</div>