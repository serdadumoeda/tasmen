@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
        <div class="flex items-start">
            <div class="flex-shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle h-5 w-5 text-red-500"></i>
            </div>
            <div class="ml-3">
                <strong class="font-bold">Oops! Ada yang salah:</strong>
                <ul class="mt-1.5 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="space-y-8">

    <fieldset class="border p-4 rounded-md">
        <legend class="text-lg font-semibold px-2">Informasi Dasar Proyek</legend>
        <div class="p-4 space-y-6">
            <div>
                <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">
                    <i class="fas fa-file-signature mr-2 text-gray-500"></i> Nama Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('name', $project->name ?? '') }}" required autofocus>
            </div>

            <div>
                <label for="description" class="block font-semibold text-sm text-gray-700 mb-1">
                    <i class="fas fa-align-left mr-2 text-gray-500"></i> Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>{{ old('description', $project->description ?? '') }}</textarea>
            </div>

            <div>
                <label for="surat_ids" class="block font-semibold text-sm text-gray-700 mb-1">
                    <i class="fas fa-gavel mr-2 text-gray-500"></i> Dasar Surat (Opsional)
                </label>
                <select name="surat_ids[]" id="surat_ids" multiple class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 tom-select">
                    @php
                        $projectSuratIds = collect(old('surat_ids', isset($project) ? $project->surat->pluck('id')->all() : []));
                    @endphp
                    @foreach($suratList as $surat)
                        <option value="{{ $surat->id }}" @selected($projectSuratIds->contains($surat->id))>
                            {{ $surat->nomor_surat ?? 'No. Belum Ada' }} - {{ Str::limit($surat->perihal, 50) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih satu atau lebih surat sebagai dasar hukum kegiatan.</p>
            </div>
        </div>
    </fieldset>

    <fieldset class="border p-4 rounded-md">
        <legend class="text-lg font-semibold px-2">Jadwal Kegiatan</legend>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="start_date" class="block font-semibold text-sm text-gray-700 mb-1">
                    <i class="fas fa-calendar-alt mr-2 text-gray-500"></i> Tanggal Mulai
                </label>
                <input type="date" name="start_date" id="start_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('start_date', isset($project->start_date) ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}">
            </div>
            <div>
                <label for="end_date" class="block font-semibold text-sm text-gray-700 mb-1">
                    <i class="fas fa-calendar-check mr-2 text-gray-500"></i> Tanggal Selesai
                </label>
                <input type="date" name="end_date" id="end_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('end_date', isset($project->end_date) ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '') }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="border p-4 rounded-md">
        <legend class="text-lg font-semibold px-2">Tim Kegiatan</legend>
        <div class="p-4 space-y-6">
            <div>
                <label for="leader_id" class="block font-semibold text-sm text-gray-700 mb-1">
                    <i class="fas fa-user-tie mr-2 text-gray-500"></i> Pimpinan Kegiatan <span class="text-red-500">*</span>
                </label>
                <select name="leader_id" id="leader_id" class="tom-select" required placeholder="Pilih Pimpinan Kegiatan...">
                    @foreach ($potentialMembers as $member)
                        <option value="{{ $member->id }}" @selected(old('leader_id', $project->leader_id ?? '') == $member->id)>
                            {{ $member->name }}{{ $member->roles->isNotEmpty() ? ' (' . $member->roles->first()->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div id="leaderWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="members" class="block font-semibold text-sm text-gray-700">
                        <i class="fas fa-users-line mr-2 text-gray-500"></i> Anggota Tim <span class="text-red-500">*</span>
                    </label>
                    @if ($project->exists)
                    <button type="button" id="showMemberModalBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                        <i class="fas fa-user-plus mr-2"></i> Tambah Anggota
                    </button>
                    @endif
                </div>

                <select name="members[]" id="members" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 tom-select" multiple required placeholder="Pilih Anggota Tim...">
                    @php
                        $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id')->all() : []));
                    @endphp
                    @foreach ($potentialMembers as $member)
                        <option value="{{ $member->id }}" @selected($projectMemberIds->contains($member->id))>
                            {{ $member->name }}{{ $member->roles->isNotEmpty() ? ' (' . $member->roles->first()->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div id="membersWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div>
            </div>
        </div>
    </fieldset>
</div>

{{-- MODAL PEMILIHAN ANGGOTA --}}
<div id="memberSelectionModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
            <p class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-users-medical mr-3 text-indigo-600"></i> Pilih Anggota
            </p>
            <button type="button" id="closeMemberModalBtn" class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-people-arrows-left-right mr-2 text-green-600"></i> Tim Terbuka (Direkomendasikan)
                </h3>
                <p class="text-sm text-gray-600 mb-3">Anggota yang tersedia tanpa perlu persetujuan.</p>
                <div id="resourcePoolContainer" class="border border-gray-200 rounded-lg p-3 space-y-2 overflow-y-auto bg-gray-50 shadow-inner" style="max-height: 300px;">
                    <p class="text-center text-gray-500 p-4">Memuat...</p>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-magnifying-glass-chart mr-2 text-purple-600"></i> Cari & Minta dari Tim Lain
                </h3>
                <p class="text-sm text-gray-600 mb-3">Memerlukan persetujuan dari atasan yang bersangkutan.</p>
                <input type="text" id="userSearchInput" placeholder="Ketik nama untuk mencari..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 text-sm">
                <div id="userSearchResults" class="border border-gray-200 rounded-lg p-3 space-y-2 mt-3 overflow-y-auto bg-gray-50 shadow-inner" style="max-height: 258px;">
                    <p class="text-center text-gray-500 p-4">Hasil pencarian akan muncul di sini.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6 border-t border-gray-200 mt-6">
            <button type="button" id="addMemberFromModalBtn" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-user-plus mr-2"></i> Tambahkan Anggota Terpilih
            </button>
        </div>
    </div>
</div>