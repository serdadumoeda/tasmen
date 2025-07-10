<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Resource Pool (Tim Terbuka)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-gray-600 mb-4">
                        Pilih anggota tim Anda yang dapat dipinjam oleh unit kerja lain untuk berkolaborasi dalam proyek.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan Ketersediaan</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tersedia di Pool?</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($teamMembers as $member)
                                <tr id="member-{{ $member->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $member->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" class="notes-input block w-full rounded-md shadow-sm border-gray-300 sm:text-sm"
                                               data-member-id="{{ $member->id }}"
                                               value="{{ $member->pool_availability_notes }}"
                                               placeholder="Contoh: Ahli UI/UX, 5 jam/minggu">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{-- Switch dengan gaya Tailwind --}}
                                        <label for="poolSwitch{{ $member->id }}" class="flex items-center cursor-pointer justify-center">
                                            <div class="relative">
                                                <input type="checkbox" id="poolSwitch{{ $member->id }}" class="sr-only pool-toggle"
                                                       data-member-id="{{ $member->id }}"
                                                       {{ $member->is_in_resource_pool ? 'checked' : '' }}>
                                                <div class="block bg-gray-200 w-14 h-8 rounded-full"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                                            </div>
                                        </label>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center px-6 py-4 whitespace-nowrap">Anda tidak memiliki anggota tim untuk dikelola.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CSS untuk toggle switch --}}
    @push('styles')
    <style>
        input:checked ~ .dot {
            transform: translateX(100%);
            background-color: #48bb78; /* green-500 */
        }
        input:checked ~ .block {
            background-color: #a7f3d0; /* green-200 */
        }
    </style>
    @endpush

    {{-- JavaScript untuk AJAX --}}
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi untuk menangani update
        function updateMemberStatus(memberId) {
            const isChecked = document.getElementById(`poolSwitch${memberId}`).checked;
            const notes = document.querySelector(`#member-${memberId} .notes-input`).value;
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
                    // Kembalikan toggle jika gagal
                    document.getElementById(`poolSwitch${memberId}`).checked = !isChecked;
                }
                // Anda bisa menambahkan notifikasi sukses di sini jika perlu
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
            });
        }

        // Event listener untuk setiap toggle switch
        document.querySelectorAll('.pool-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const memberId = this.getAttribute('data-member-id');
                updateMemberStatus(memberId);
            });
        });

        // Event listener untuk setiap input catatan (dengan debounce)
        let debounceTimer;
        document.querySelectorAll('.notes-input').forEach(input => {
            input.addEventListener('keyup', function() {
                const memberId = this.getAttribute('data-member-id');
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    updateMemberStatus(memberId);
                }, 800); // Tunggu 800ms setelah user berhenti mengetik
            });
        });
    });
    </script>
    @endpush
</x-app-layout>