import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;


const initProjectForm = () => {
    const projectFormContainer = document.querySelector('form[action*="/projects"]');
    const showBtn = document.getElementById('showResourcePoolBtn');
    if (!projectFormContainer || !showBtn) return;

    console.log("✔️ Halaman Form Proyek Terdeteksi. Menginisialisasi fitur 'Tim Terbuka'...");

    const modal = document.getElementById('resourcePoolModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const addBtn = document.getElementById('addMembersFromPoolBtn');
    const membersContainer = document.getElementById('resourcePoolMembers');
    const membersSelect = document.getElementById('members');

    if (!modal || !closeBtn || !addBtn || !membersSelect) {
        console.error("❌ Kesalahan: Elemen modal atau tombol di dalamnya tidak ditemukan.");
        return;
    }

    // Fungsi untuk membuka modal (sama seperti sebelumnya)
    showBtn.addEventListener('click', function() { /* ... kode ini tidak berubah ... */ });

    // Fungsi untuk menutup modal (sama seperti sebelumnya)
    const closeModal = () => modal.classList.add('hidden');
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // --- FUNGSI UNTUK MENAMBAHKAN ANGGOTA / MEMBUAT PERMINTAAN ---
    addBtn.addEventListener('click', function() {
        const selectedCheckbox = document.querySelector('.pool-member-select:checked');
        if (!selectedCheckbox) {
            alert('Silakan pilih satu anggota.');
            return;
        }

        const memberId = selectedCheckbox.value;
        const memberName = selectedCheckbox.getAttribute('data-name');
        const isInPool = selectedCheckbox.hasAttribute('data-in-pool');

        if (isInPool) {
            // --- SKENARIO 1: Anggota ada di Resource Pool (sama seperti Fase 1) ---
            console.log(`➕ Menambahkan anggota dari pool: ${memberName}`);
            if (!membersSelect.querySelector(`option[value="${memberId}"]`)) {
                const newOption = document.createElement('option');
                newOption.value = memberId;
                newOption.textContent = memberName;
                membersSelect.appendChild(newOption);
            }
            membersSelect.querySelector(`option[value="${memberId}"]`).selected = true;
            closeModal();
        } else {
            // --- SKENARIO 2: Anggota TIDAK di Resource Pool -> Buat Permintaan ---
            const message = prompt(`Anda akan mengirim permintaan untuk meminjam "${memberName}".\nTambahkan pesan untuk atasan mereka (opsional):`);
            
            // Dapatkan project_id dari URL form (untuk create/edit)
            const projectId = projectFormContainer.action.split('/').slice(-1)[0];

            console.log(`📨 Mengirim permintaan untuk meminjam: ${memberName}`);

            fetch('{{ route("peminjaman-requests.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    project_id: projectId, // Perlu penyesuaian jika route berbeda
                    requested_user_id: memberId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModal();
                } else {
                    alert('Gagal mengirim permintaan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi saat mengirim permintaan.');
            });
        }
    });

    // --- Fungsi untuk mencari SEMUA user ---
    const searchInput = document.getElementById('userSearchInput');
    searchInput.addEventListener('keyup', function() {
        // Logika untuk fetch dan menampilkan hasil pencarian user
        // (Mirip dengan fetch resource pool, tapi ke endpoint pencarian user)
    });
};


const initResourcePool = () => {
    // Cek apakah kita berada di halaman yang benar dengan mencari elemen uniknya
    const resourcePoolTable = document.querySelector('table .pool-toggle');
    if (!resourcePoolTable) {
        return; // Jika tidak ditemukan, hentikan eksekusi
    }

    console.log("✔️ Halaman Resource Pool terdeteksi, script dijalankan.");

    // Fungsi untuk mengirim update ke server
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
        .then(response => {
            if (!response.ok) {
                // Jika terjadi error, kita coba baca pesan dari server
                return response.json().then(err => { throw new Error(err.message) });
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                alert('Gagal memperbarui: ' + data.message);
                document.getElementById(`poolSwitch${memberId}`).checked = !isChecked;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        });
    }

    // Pasang event listener ke setiap toggle switch
    document.querySelectorAll('.pool-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const memberId = this.getAttribute('data-member-id');
            updateMemberStatus(memberId);
        });
    });

    // Pasang event listener ke setiap input catatan
    let debounceTimer;
    document.querySelectorAll('.notes-input').forEach(input => {
        input.addEventListener('keyup', function() {
            const memberId = this.getAttribute('data-member-id');
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                updateMemberStatus(memberId);
            }, 800); // Kirim update 800ms setelah user berhenti mengetik
        });
    });
};

// Menjalankan Alpine.js dan fungsi inisialisasi kita setelah halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    initProjectForm();
});

Alpine.start();