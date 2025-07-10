import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ======================================================================
// FUNGSI UNTUK FITUR "WORKLOAD INSIGHT" PADA FORM PIMPINAN PROYEK
// ======================================================================
const initWorkloadInsight = () => {
    const leaderSelect = document.getElementById('leader_id');
    const workloadInfoContainer = document.getElementById('leaderWorkloadInfo');

    if (!leaderSelect || !workloadInfoContainer) {
        return; // Hentikan jika form pimpinan proyek tidak ada di halaman ini
    }

    console.log("✔️ Fitur 'Workload Insight' diinisialisasi.");

    leaderSelect.addEventListener('change', function() {
        const selectedUserId = this.value;

        if (!selectedUserId) {
            workloadInfoContainer.innerHTML = '';
            return;
        }

        workloadInfoContainer.innerHTML = `<p class="text-gray-500 italic">Memeriksa beban kerja...</p>`;

        // PERBAIKAN: Gunakan URL statis yang benar
        fetch(`/api/users/${selectedUserId}/workload`)
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data beban kerja.');
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    workloadInfoContainer.innerHTML = `
                        <div class="p-3 mt-2 bg-gray-50 border rounded-md">
                            <h4 class="font-semibold text-gray-800 mb-2">Ringkasan Beban Kerja:</h4>
                            <ul class="space-y-1 text-gray-700">
                                <li class="flex items-center"><i class="fas fa-briefcase text-blue-500 fa-fw w-5 mr-2"></i> ${data.active_projects} Proyek Aktif</li>
                                <li class="flex items-center"><i class="fas fa-bolt text-yellow-500 fa-fw w-5 mr-2"></i> ${data.active_adhoc_tasks} Tugas Harian</li>
                                <li class="flex items-center"><i class="fas fa-file-signature text-green-500 fa-fw w-5 mr-2"></i> ${data.active_sks} SK Aktif</li>
                            </ul>
                        </div>`;
                } else {
                    workloadInfoContainer.innerHTML = `<p class="text-red-500">Gagal memuat info beban kerja.</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching workload:', error);
                workloadInfoContainer.innerHTML = `<p class="text-red-500">Terjadi kesalahan koneksi.</p>`;
            });
    });
};

// ======================================================================
// FUNGSI UNTUK FITUR MODAL "PILIH DARI TIM TERBUKA"
// ======================================================================
const initTeamSelectionModal = () => {
    const showBtn = document.getElementById('showResourcePoolBtn');
    if (!showBtn) {
        return; // Hentikan jika tombol tidak ada
    }

    console.log("✔️ Fitur Modal 'Tim Terbuka' diinisialisasi.");

    const modal = document.getElementById('resourcePoolModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const addBtn = document.getElementById('addMembersFromPoolBtn');
    const membersContainer = document.getElementById('resourcePoolMembers');
    const membersSelect = document.getElementById('members');

    if (!modal || !closeBtn || !addBtn || !membersSelect) {
        console.error("❌ Kesalahan: Elemen modal atau tombol di dalamnya tidak ditemukan.");
        return;
    }

    showBtn.addEventListener('click', function() {
        modal.classList.remove('hidden');
        membersContainer.innerHTML = '<tr><td colspan="4" class="text-center p-4">Memuat data...</td></tr>';
        
        // PERBAIKAN: Gunakan URL statis yang benar
        fetch('/api/resource-pool/members')
            .then(response => response.json())
            .then(members => {
                membersContainer.innerHTML = '';
                if (members.length === 0) {
                    membersContainer.innerHTML = '<tr><td colspan="4" class="text-center p-4">Tidak ada anggota yang tersedia.</td></tr>';
                    return;
                }
                members.forEach(member => {
                    let unit = member.role || 'N/A';
                    let row = `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300 shadow-sm pool-member-select" value="${member.id}" data-name="${member.name} (Tim Terbuka)"></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">${member.name}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">${unit}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">${member.pool_availability_notes || ''}</td>
                        </tr>`;
                    membersContainer.insertAdjacentHTML('beforeend', row);
                });
            });
    });

    const closeModal = () => modal.classList.add('hidden');
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    addBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.pool-member-select:checked');
        selectedCheckboxes.forEach(checkbox => {
            const memberId = checkbox.value;
            const memberName = checkbox.getAttribute('data-name');
            if (!membersSelect.querySelector(`option[value="${memberId}"]`)) {
                const newOption = document.createElement('option');
                newOption.value = memberId;
                newOption.textContent = memberName;
                membersSelect.appendChild(newOption);
            }
            const optionToSelect = membersSelect.querySelector(`option[value="${memberId}"]`);
            if(optionToSelect) optionToSelect.selected = true;
        });
        closeModal();
    });
};

// ======================================================================
// FUNGSI UNTUK HALAMAN MANAJEMEN RESOURCE POOL
// ======================================================================
const initResourcePoolPage = () => {
    const resourcePoolTable = document.querySelector('table .pool-toggle');
    if (!resourcePoolTable) {
        return;
    }

    console.log("✔️ Halaman Manajemen Resource Pool terdeteksi.");

    function updateMemberStatus(memberId) {
        const isChecked = document.getElementById(`poolSwitch${memberId}`).checked;
        const notesInput = document.querySelector(`tr#member-${memberId} .notes-input`);
        const notes = notesInput ? notesInput.value : '';
        const url = `/resource-pool/update/${memberId}`;

        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                is_in_resource_pool: isChecked,
                pool_availability_notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Gagal memperbarui: ' + data.message);
                document.getElementById(`poolSwitch${memberId}`).checked = !isChecked;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
        });
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

const initTeamWorkloadInsight = () => {
    const membersSelect = document.getElementById('members');
    const workloadInfoContainer = document.getElementById('membersWorkloadInfo');

    if (!membersSelect || !workloadInfoContainer) {
        return; // Hentikan jika form anggota tim tidak ada
    }

    console.log("✔️ Fitur 'Workload Insight' untuk Anggota Tim diinisialisasi.");

    let lastSelectedValues = []; // Simpan pilihan sebelumnya

    membersSelect.addEventListener('change', function() {
        const currentSelectedValues = Array.from(this.selectedOptions).map(option => option.value);
        
        // Temukan anggota yang baru saja ditambahkan
        const newlySelected = currentSelectedValues.filter(id => !lastSelectedValues.includes(id));
        
        // Update pilihan sebelumnya
        lastSelectedValues = currentSelectedValues;

        // Jika tidak ada anggota baru yang dipilih (misal: saat menghapus), kosongkan info
        if (newlySelected.length === 0) {
            workloadInfoContainer.innerHTML = '';
            return;
        }

        // Ambil ID anggota terakhir yang dipilih untuk ditampilkan bebannya
        const lastSelectedUserId = newlySelected[newlySelected.length - 1];

        if (!lastSelectedUserId) {
            workloadInfoContainer.innerHTML = '';
            return;
        }

        workloadInfoContainer.innerHTML = `<p class="text-gray-500 italic">Memeriksa beban kerja anggota terakhir...</p>`;

        fetch(`/api/users/${lastSelectedUserId}/workload`)
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data beban kerja.');
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    const userName = this.querySelector(`option[value="${lastSelectedUserId}"]`).textContent.split('(')[0].trim();
                    workloadInfoContainer.innerHTML = `
                        <div class="p-3 mt-2 bg-gray-50 border rounded-md">
                            <h4 class="font-semibold text-gray-800 mb-2">Ringkasan Beban Kerja: <span class="font-normal">${userName}</span></h4>
                            <ul class="space-y-1 text-gray-700">
                                <li class="flex items-center"><i class="fas fa-briefcase text-blue-500 fa-fw w-5 mr-2"></i> ${data.active_projects} Proyek Aktif</li>
                                <li class="flex items-center"><i class="fas fa-bolt text-yellow-500 fa-fw w-5 mr-2"></i> ${data.active_adhoc_tasks} Tugas Harian</li>
                                <li class="flex items-center"><i class="fas fa-file-signature text-green-500 fa-fw w-5 mr-2"></i> ${data.active_sks} SK Aktif</li>
                            </ul>
                        </div>`;
                } else {
                    workloadInfoContainer.innerHTML = `<p class="text-red-500">Gagal memuat info beban kerja.</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching workload:', error);
                workloadInfoContainer.innerHTML = `<p class="text-red-500">Terjadi kesalahan koneksi.</p>`;
            });
    });
};

// ======================================================================
// JALANKAN SEMUA FUNGSI INISIALISASI SETELAH HALAMAN DIMUAT
// ======================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Jalankan semua fungsi inisialisasi.
    // Setiap fungsi akan memeriksa sendiri apakah perlu dijalankan atau tidak.
    initWorkloadInsight();
    initTeamSelectionModal();
    initResourcePoolPage();
    initTeamWorkloadInsight();
    
    Alpine.start();
});