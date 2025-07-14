<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Proyek Baru (Langkah 2 dari 2): Bentuk Tim
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('projects.store.step2', $project) }}" method="POST">
                        @csrf
                        <h3 class="text-lg font-medium text-gray-900">Proyek: {{ $project->name }}</h3>
                        <p class="text-sm text-gray-600 mb-4">Pilih Pimpinan Proyek dan Anggota Tim. Anda sekarang dapat mencari anggota dari luar hierarki Anda.</p>
                        
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
                                    <option value="{{ $member->id }}" @selected($projectMemberIds->contains($member->id))>
                                        {{ $member->name }} ({{ $member->role }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Tahan tombol Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu anggota.</p>
                            <div id="membersWorkloadInfo" class="mt-2 text-sm"></div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Simpan Tim & Selesaikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Memuat modal dari file partial --}}
    @include('projects.partials.modal-members')
</x-app-layout>