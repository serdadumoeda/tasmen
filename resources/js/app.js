import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ======================================================================
// FUNGSI UNTUK MENAMPILKAN INFO BEBAN KERJA (GABUNGAN)
// ======================================================================
const initWorkloadInsight = () => {
    const setupListener = (selectId, containerId) => {
        const selectElement = document.getElementById(selectId);
        const infoContainer = document.getElementById(containerId);

        if (!selectElement || !infoContainer) return;

        let lastValues = [];
        selectElement.addEventListener('change', function() {
            let userId;
            if (this.multiple) {
                const currentValues = Array.from(this.selectedOptions).map(opt => opt.value);
                const newlySelected = currentValues.filter(id => !lastValues.includes(id));
                lastValues = currentValues;
                if (newlySelected.length === 0) {
                    infoContainer.innerHTML = '';
                    return;
                }
                userId = newlySelected[newlySelected.length - 1];
            } else {
                userId = this.value;
            }

            if (!userId) {
                infoContainer.innerHTML = '';
                return;
            }

            infoContainer.innerHTML = `<p class="text-gray-500 italic mt-2">Memeriksa beban kerja...</p>`;

            fetch(`/api/users/${userId}/workload`)
                .then(response => {
                    if (!response.ok) return response.json().then(err => { throw new Error(err.message || 'Gagal mengambil data.') });
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        const data = result.data;
                        const userName = this.querySelector(`option[value="${userId}"]`).textContent.split('(')[0].trim();
                        let skillsHtml = '<p class="text-xs text-gray-500 italic">Belum ada portofolio keahlian.</p>';
                        if (data.skills && data.skills.length > 0) {
                            skillsHtml = data.skills.map(skill => `<span class="inline-block bg-teal-100 text-teal-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">${skill}</span>`).join('');
                        }

                        infoContainer.innerHTML = `
                        <div class="p-3 mt-2 bg-gray-50 border rounded-md">
                            <h4 class="font-semibold text-gray-800 mb-2">Ringkasan Beban Kerja: <span class="font-normal">${userName}</span></h4>
                            <ul class="space-y-1 text-gray-700 text-sm">
                                <li class="flex items-center"><i class="fas fa-briefcase text-blue-500 fa-fw w-5 mr-2"></i> ${data.active_projects} Proyek Aktif</li>
                                <li class="flex items-center"><i class="fas fa-bolt text-yellow-500 fa-fw w-5 mr-2"></i> ${data.active_adhoc_tasks} Tugas Harian</li>
                                <li class="flex items-center"><i class="fas fa-file-signature text-green-500 fa-fw w-5 mr-2"></i> ${data.active_sks} SK Aktif</li>
                            </ul>
                        </div>`;
                } else {
                     infoContainer.innerHTML = `<p class="text-red-500 mt-2">Gagal memuat info: ${result.message}</p>`;
                }
            })
                .catch(error => {
                    console.error('Error fetching workload:', error);
                    infoContainer.innerHTML = `<p class="text-red-500 mt-2">Terjadi kesalahan: ${error.message}</p>`;
                });
        });
    };

    console.log("✔️ Menginisialisasi fitur 'Workload Insight'...");
    setupListener('leader_id', 'leaderWorkloadInfo');
    setupListener('members', 'membersWorkloadInfo');
};

// ======================================================================
// FUNGSI UNTUK MODAL PEMILIHAN ANGGOTA
// ======================================================================
const initMemberSelectionModal = () => {
    const showBtn = document.getElementById('showMemberModalBtn');
    if (!showBtn) return;

    const modal = document.getElementById('memberSelectionModal');
    const closeBtn = document.getElementById('closeMemberModalBtn');
    const addBtn = document.getElementById('addMemberFromModalBtn');
    const poolContainer = document.getElementById('resourcePoolContainer');
    const searchInput = document.getElementById('userSearchInput');
    const searchResultsContainer = document.getElementById('userSearchResults');
    const membersSelect = document.getElementById('members');

    if (!modal || !closeBtn || !addBtn || !poolContainer || !searchInput || !searchResultsContainer || !membersSelect) {
        console.error("Satu atau lebih elemen untuk modal anggota tidak ditemukan.");
        return;
    }
    
    console.log("✔️ Fitur Modal Pemilihan Anggota diinisialisasi.");

    const renderUserRow = (user, type) => {
        let note = type === 'pool' ? `<span class="text-green-600 font-semibold">Tersedia</span>` : `<span class="text-orange-600 font-semibold">Butuh Persetujuan</span>`;
        return `
            <label class="flex items-center p-2 rounded-md hover:bg-gray-100 cursor-pointer">
                <input type="radio" name="modal_member_selection" class="h-4 w-4 border-gray-300 member-radio" value="${user.id}" data-name="${user.name} (${user.role})" data-type="${type}">
                <span class="ml-3 text-sm text-gray-800">${user.name} <span class="text-gray-500">(${user.role})</span></span>
                <span class="ml-auto text-xs">${note}</span>
            </label>`;
    };

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
                        poolContainer.insertAdjacentHTML('beforeend', renderUserRow(member, 'pool'));
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
                            searchResultsContainer.insertAdjacentHTML('beforeend', renderUserRow(user, 'request'));
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
            
        if (membersSelect.querySelector(`option[value="${memberId}"]`)) {
            alert(`${memberName} sudah ada di dalam tim.`);
            return;
        }

        if (type === 'pool') {
            const newOption = document.createElement('option');
            newOption.value = memberId;
            newOption.textContent = memberName;
            newOption.selected = true;
            membersSelect.appendChild(newOption);
            membersSelect.dispatchEvent(new Event('change'));
        } else {
            sendBorrowRequest(memberId, memberName);
        }
        
        closeModal();
    });

    function sendBorrowRequest(memberId, memberName) {
        // --- BAGIAN YANG DIPERBAIKI ---
        // Mengambil ID proyek dari URL halaman saat ini, bukan dari form action.
        // Ini akan bekerja baik di halaman 'create step 2' maupun 'edit'.
        let projectId;
        const match = window.location.pathname.match(/\/projects\/(\d+)/);
        if (match && match[1]) {
            projectId = match[1];
        }

        if (!projectId) {
            alert("Tidak dapat menemukan ID Proyek dari URL. Fitur ini tidak dapat dilanjutkan.");
            console.error("Gagal mengekstrak ID Proyek dari URL:", window.location.pathname);
            return;
        }
        // --- AKHIR PERBAIKAN ---

        let message = prompt(`Anda akan mengirim permintaan untuk meminjam "${memberName}".\nTambahkan pesan untuk atasan mereka (opsional):`);
        
        // Cek jika pengguna menekan tombol "Cancel" pada prompt
        if (message === null) {
            return; // Hentikan fungsi jika permintaan dibatalkan
        }

        fetch(`/peminjaman-requests`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ project_id: projectId, requested_user_id: memberId, message: message })
        })
        .then(response => {
            if (!response.ok) {
                 // Jika respons tidak OK, coba baca pesan error dari JSON
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(`Permintaan untuk ${memberName} telah terkirim.`);
            } else {
                // Pesan error dari server (jika ada) akan ditampilkan di sini
                alert(`Gagal mengirim permintaan: ${data.message || 'Terjadi kesalahan yang tidak diketahui.'}`);
            }
        })
        .catch(error => {
            // Menangkap error dari fetch atau dari Promise.reject
            console.error('Error sending borrow request:', error);
            alert(`Gagal mengirim permintaan: ${error.message || 'Terjadi kesalahan koneksi.'}`);
        });
    }
};

// ======================================================================
// FUNGSI UNTUK HALAMAN MANAJEMEN RESOURCE POOL
// ======================================================================
const initResourcePoolPage = () => {
    const resourcePoolTable = document.querySelector('table .pool-toggle');
    if (!resourcePoolTable) return;
    
    console.log("✔️ Halaman Manajemen Resource Pool diinisialisasi.");
    
    function updateMemberStatus(memberId) {
        const isChecked = document.getElementById(`poolSwitch${memberId}`).checked;
        const notesInput = document.querySelector(`tr#member-${memberId} .notes-input`);
        const notes = notesInput ? notesInput.value : '';
        const url = `/resource-pool/update/${memberId}`;

        fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ is_in_resource_pool: isChecked, pool_availability_notes: notes })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Gagal memperbarui: ' + data.message);
                document.getElementById(`poolSwitch${memberId}`).checked = !isChecked;
            }
        })
        .catch(error => { console.error('Error:', error); alert('Terjadi kesalahan koneksi.'); });
    }

    document.querySelectorAll('.pool-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            updateMemberStatus(this.getAttribute('data-member-id'));
        });
    });

    let debounceTimer;
    document.querySelectorAll('.notes-input').forEach(input => {
        input.addEventListener('keyup', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                updateMemberStatus(this.getAttribute('data-member-id'));
            }, 800);
        });
    });
};


// ======================================================================
// JALANKAN SEMUA FUNGSI INISIALISASI SETELAH HALAMAN DIMUAT
// ======================================================================
document.addEventListener('DOMContentLoaded', () => {
    initWorkloadInsight();
    initMemberSelectionModal();
    initResourcePoolPage();
    
    Alpine.start();
});