<div id="memberSelectionModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center"> {{-- Backdrop gelap, tengah vertikal/horizontal --}}
    {{-- MENGHAPUS x-transition dan kelas opacity/scale --}}
    <div class="relative mx-auto p-8 border w-full max-w-4xl shadow-2xl rounded-xl bg-white"> {{-- Modal lebih besar, shadow-2xl, rounded-xl --}}
        <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4"> {{-- Border bawah, margin bawah --}}
            <p class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-users-medical mr-3 text-indigo-600"></i> Tambah Anggota Tim
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