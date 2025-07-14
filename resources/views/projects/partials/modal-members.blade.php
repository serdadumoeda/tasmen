<div id="memberSelectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">Tambah Anggota Tim</p>
            <button type="button" id="closeMemberModalBtn" class="cursor-pointer z-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-800">Tim Terbuka (Direkomendasikan)</h3>
                <p class="text-xs text-gray-500 mb-2">Anggota yang tersedia tanpa perlu persetujuan.</p>
                <div id="resourcePoolContainer" class="border rounded-md p-2 space-y-1 overflow-y-auto" style="max-height: 300px;"><p class="text-center text-gray-400 p-4">Memuat...</p></div>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Cari & Minta dari Tim Lain</h3>
                <p class="text-xs text-gray-500 mb-2">Memerlukan persetujuan dari atasan yang bersangkutan.</p>
                <input type="text" id="userSearchInput" placeholder="Ketik nama untuk mencari..." class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <div id="userSearchResults" class="border rounded-md p-2 space-y-1 mt-2 overflow-y-auto" style="max-height: 258px;"><p class="text-center text-gray-400 p-4">Hasil pencarian akan muncul di sini.</p></div>
            </div>
        </div>
        <div class="flex justify-end pt-4 border-t mt-4">
            <button type="button" id="addMemberFromModalBtn" class="px-4 py-2 bg-gray-800 text-white text-base font-medium rounded-md hover:bg-gray-700">Tambahkan Anggota Terpilih</button>
        </div>
    </div>
</div>