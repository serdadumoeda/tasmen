<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Kegiatan Baru (Langkah 2 dari 2): Bentuk Tim') }}
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    <div class="py-8 bg-gray-50"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mengubah shadow-sm menjadi shadow-xl dan memastikan rounded-lg --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('projects.store.step2', $project) }}" method="POST">
                        @csrf
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

                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-project-diagram mr-2 text-indigo-600"></i> Kegiatan: {{ $project->name }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">Pilih Pimpinan Kegiatan dan Anggota Tim. Anda sekarang dapat mencari anggota dari luar hierarki Anda.</p>
                        
                        <div class="space-y-6"> {{-- Group form elements for consistent spacing --}}
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
                                <div class="flex justify-between items-center mb-2">
                                    <label for="members" class="block font-semibold text-sm text-gray-700">
                                        <i class="fas fa-users-line mr-2 text-gray-500"></i> Anggota Tim <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button" id="showMemberModalBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                        <i class="fas fa-user-plus mr-2"></i> Tambah Anggota
                                    </button>
                                </div>
                                <select name="members[]" id="members" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 tom-select" multiple required placeholder="Pilih anggota tim...">
                                    @php
                                        $projectMemberIds = collect(old('members', isset($project) ? $project->members->pluck('id')->all() : []));
                                    @endphp
                                    @foreach ($potentialMembers as $member)
                                        @php
                                            $displayName = "{$member->name} ({$member->role})";
                                            $isExternal = isset($subordinateIds) && !$subordinateIds->contains($member->id);
                                            $request = isset($loanRequests) && $loanRequests->has($member->id) ? $loanRequests->get($member->id) : null;
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
                                <div id="membersWorkloadInfo" class="mt-2 text-sm text-gray-600 p-2 bg-gray-50 rounded-md shadow-sm"></div> {{-- Styling for workload info div --}}
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                Simpan Tim & Selesaikan <i class="fas fa-check-circle ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Memuat modal dari file partial --}}
    @include('projects.partials.modal-members')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize TomSelect for all elements with class 'tom-select'
            document.querySelectorAll('.tom-select').forEach(element => {
                new TomSelect(element, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: null,
                    placeholder: 'Pilih Anggota Tim'
                });
            });

            // Logic for modal opening/closing (copied from projects.partials.form)
            const showBtn = document.getElementById('showMemberModalBtn');
            const modal = document.getElementById('memberSelectionModal');
            const closeBtn = document.getElementById('closeMemberModalBtn');
            const addBtn = document.getElementById('addMemberFromModalBtn'); // Assuming this button still exists and adds members
            const poolContainer = document.getElementById('resourcePoolContainer');
            const searchInput = document.getElementById('userSearchInput');
            const searchResultsContainer = document.getElementById('userSearchResults');
            const membersSelect = document.getElementById('members'); // The main TomSelect instance for project members

            if (showBtn && modal && closeBtn && addBtn && poolContainer && searchInput && searchResultsContainer && membersSelect) {
                showBtn.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    poolContainer.innerHTML = `<p class="text-center text-gray-400 p-4">Memuat...</p>`;
                    fetch('/api/resource-pool/members')
                        .then(response => response.json())
                        .then(members => {
                            poolContainer.innerHTML = '';
                            if (members.length === 0) {
                                poolContainer.innerHTML = `<p class="text-center text-gray-400 p-4">Tidak ada anggota di Tim Terbuka.</p>`;
                            } else {
                                members.forEach(member => {
                                    poolContainer.insertAdjacentHTML('beforeend', `
                                        <label class="flex items-center p-2 rounded-md hover:bg-gray-100 cursor-pointer">
                                            <input type="radio" name="modal_member_selection" class="h-4 w-4 border-gray-300 member-radio" value="${member.id}" data-name="${member.name} (${member.role})" data-type="pool">
                                            <span class="ml-3 text-sm text-gray-800">${member.name} <span class="text-gray-500">(${member.role})</span></span>
                                            <span class="ml-auto text-xs text-green-600 font-semibold">Tersedia</span>
                                        </label>
                                    `);
                                });
                            }
                        });
                });

                const closeModal = () => modal.classList.add('hidden');
                closeBtn.addEventListener('click', closeModal);

                let searchTimeout;
                searchInput.addEventListener('keyup', () => {
                    clearTimeout(searchTimeout);
                    const query = searchInput.value;
                    if (query.length < 3) {
                        searchResultsContainer.innerHTML = `<p class="text-center text-gray-400 p-4">Ketik min. 3 huruf untuk mencari.</p>`;
                        return;
                    }
                    searchResultsContainer.innerHTML = `<p class="text-center text-gray-400 p-4">Mencari...</p>`;
                    searchTimeout = setTimeout(() => {
                        fetch(`/api/users/search?q=${query}`)
                            .then(response => response.json())
                            .then(users => {
                                searchResultsContainer.innerHTML = '';
                                if (users.length === 0) {
                                    searchResultsContainer.innerHTML = `<p class="text-center text-gray-400 p-4">Tidak ada pengguna ditemukan.</p>`;
                                } else {
                                    users.forEach(user => {
                                        searchResultsContainer.insertAdjacentHTML('beforeend', `
                                            <label class="flex items-center p-2 rounded-md hover:bg-gray-100 cursor-pointer">
                                                <input type="radio" name="modal_member_selection" class="h-4 w-4 border-gray-300 member-radio" value="${user.id}" data-name="${user.name} (${user.role})" data-type="request">
                                                <span class="ml-3 text-sm text-gray-800">${user.name} <span class="text-gray-500">(${user.role})</span></span>
                                                <span class="ml-auto text-xs text-orange-600 font-semibold">Butuh Persetujuan</span>
                                            </label>
                                        `);
                                    });
                                }
                            });
                    }, 500);
                });

                addBtn.addEventListener('click', () => {
                    const selectedRadio = document.querySelector('.member-radio:checked');
                    if (!selectedRadio) {
                        alert('Silakan pilih satu anggota untuk ditambahkan.');
                        return;
                    }
                    
                    const memberId = selectedRadio.value;
                    const memberName = selectedRadio.getAttribute('data-name');
                    const type = selectedRadio.getAttribute('data-type');
                        
                    // Get the TomSelect instance for 'members'
                    const tomSelectInstance = membersSelect.tomselect;

                    if (tomSelectInstance.getValue().includes(memberId)) { // Check if already selected in TomSelect
                        alert(`${memberName} sudah ada di dalam tim.`);
                        return;
                    }

                    if (type === 'pool') {
                        // Add directly to TomSelect
                        tomSelectInstance.addOption({value: memberId, text: memberName});
                        tomSelectInstance.addItem(memberId);
                    } else {
                        // Send borrow request, then add to TomSelect (or alert failure)
                        sendBorrowRequest(memberId, memberName, tomSelectInstance); // Pass TomSelect instance
                    }
                    
                    closeModal();
                });

                function sendBorrowRequest(memberId, memberName, tomSelectInstance) {
                    let projectId = '{{ $project->id }}'; // Project ID is available in this context

                    let message = prompt(`Anda akan mengirim permintaan untuk menugaskan "${memberName}".\nTambahkan pesan untuk atasan mereka (opsional):`);
                    
                    if (message === null) {
                        return;
                    }

                    fetch(`/peminjaman-requests`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({ project_id: projectId, requested_user_id: memberId, message: message })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(`Permintaan untuk ${memberName} telah terkirim.`);
                            // Add the member to TomSelect with a "Pending" indicator
                            tomSelectInstance.addOption({value: memberId, text: `${memberName} [Menunggu]`});
                            tomSelectInstance.addItem(memberId);
                        } else {
                            alert(`Gagal mengirim permintaan: ${data.message || 'Terjadi kesalahan yang tidak diketahui.'}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error sending borrow request:', error);
                        alert(`Gagal mengirim permintaan: ${error.message || 'Terjadi kesalahan koneksi.'}`);
                    });
                }
            } else {
                console.warn("One or more elements for member selection modal were not found on this page.");
            }
        });
    </script>
    @endpush
</x-app-layout>