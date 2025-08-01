@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
        <div class="flex items-start">
            <div class="flex-shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle h-5 w-5 text-red-500"></i> {{-- Icon peringatan --}}
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

<div class="space-y-6"> {{-- Mengubah mb-4 menjadi space-y-6 untuk konsistensi --}}
    <div>
        <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-file-signature mr-2 text-gray-500"></i> Nama Proyek <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('name', $project->name ?? '') }}" required>
    </div>

    <div>
        <label for="description" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-align-left mr-2 text-gray-500"></i> Deskripsi <span class="text-red-500">*</span>
        </label>
        <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>{{ old('description', $project->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- Mengubah gap-4 menjadi gap-6 --}}
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

    <div>
        <label for="leader_id" class="block font-semibold text-sm text-gray-700 mb-1">
            <i class="fas fa-user-tie mr-2 text-gray-500"></i> Pimpinan Proyek <span class="text-red-500">*</span>
        </label>
        <select name="leader_id" id="leader_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
            <option value="">-- Pilih Pimpinan Proyek --</option>
            @foreach ($potentialMembers as $member)
                <option value="{{ $member->id }}" @selected(old('leader_id', $project->leader_id ?? '') == $member->id)>
                    {{ $member->name }} ({{ $member->role }})
                </option>
            @endforeach
        </select>
        <div id="leaderWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div> {{-- Styling info beban kerja --}}
    </div>

    <div>
        <div class="flex justify-between items-center mb-2"> {{-- Mengubah mb-1 menjadi mb-2 --}}
            <label for="members" class="block font-semibold text-sm text-gray-700">
                <i class="fas fa-users-line mr-2 text-gray-500"></i> Anggota Tim <span class="text-red-500">*</span>
            </label>
            @if ($project->exists)
            <button type="button" id="showMemberModalBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Tambah Anggota modern --}}
                <i class="fas fa-user-plus mr-2"></i> Tambah Anggota
            </button>
            @endif
        </div>

        <select name="members[]" id="members" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 tom-select" multiple required> {{-- Menambahkan kelas tom-select --}}
            @php
                $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id')->all() : []));
            @endphp
            @foreach ($potentialMembers as $member)
                <option value="{{ $member->id }}" @selected($projectMemberIds->contains($member->id))>
                    {{ $member->name }} ({{ $member->role }})
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Tahan tombol Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu anggota.</p>
        <div id="membersWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div> {{-- Styling info beban kerja --}}
    </div>
</div>

{{-- MODAL PEMILIHAN ANGGOTA --}}
<div id="memberSelectionModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center"> {{-- Backdrop gelap, tengah vertikal/horizontal --}}
    {{-- MENGHAPUS x-transition dan kelas opacity/scale --}}
    <div class="relative mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-xl bg-white"> {{-- Modal lebih besar, shadow-2xl, rounded-xl --}}
        <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4"> {{-- Border bawah, margin bawah --}}
            <p class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-users-medical mr-3 text-indigo-600"></i> Pilih Anggota
            </p>
            <button type="button" id="closeMemberModalBtn" class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"> {{-- Tombol close lebih halus --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-8"> {{-- Gap lebih besar --}}
            {{-- KOLOM KIRI: TIM TERBUKA --}}
            <div>
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-people-arrows-left-right mr-2 text-green-600"></i> Tim Terbuka (Direkomendasikan)
                </h3>
                <p class="text-sm text-gray-600 mb-3">Anggota yang tersedia tanpa perlu persetujuan.</p>
                <div id="resourcePoolContainer" class="border border-gray-200 rounded-lg p-3 space-y-2 overflow-y-auto bg-gray-50 shadow-inner" style="max-height: 300px;"> {{-- Styling kontainer pool --}}
                    <p class="text-center text-gray-500 p-4">Memuat...</p>
                </div>
            </div>

            {{-- KOLOM KANAN: CARI & MINTA --}}
            <div>
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-magnifying-glass-chart mr-2 text-purple-600"></i> Cari & Minta dari Tim Lain
                </h3>
                <p class="text-sm text-gray-600 mb-3">Memerlukan persetujuan dari atasan yang bersangkutan.</p>
                <input type="text" id="userSearchInput" placeholder="Ketik nama untuk mencari..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 text-sm"> {{-- Input search modern --}}
                <div id="userSearchResults" class="border border-gray-200 rounded-lg p-3 space-y-2 mt-3 overflow-y-auto bg-gray-50 shadow-inner" style="max-height: 258px;"> {{-- Styling kontainer hasil pencarian --}}
                    <p class="text-center text-gray-500 p-4">Hasil pencarian akan muncul di sini.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6 border-t border-gray-200 mt-6"> {{-- Border atas, padding atas, margin atas --}}
            <button type="button" id="addMemberFromModalBtn" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Tombol Tambahkan Anggota modern --}}
                <i class="fas fa-user-plus mr-2"></i> Tambahkan Anggota Terpilih
            </button>
        </div>
    </div>
</div>