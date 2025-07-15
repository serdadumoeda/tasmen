<div class="border p-4 rounded-lg shadow-sm bg-white">
    <p>
        <strong>{{ $request->requester?->name ?? '[Pengguna Dihapus]' }}</strong> meminta untuk meminjam
        <strong>{{ $request->requestedUser?->name ?? '[Pengguna Dihapus]' }}</strong>
        untuk proyek: <strong>{{ $request->project?->name ?? '[Proyek Dihapus]' }}</strong>.
    </p>
    @if ($request->message)
        <p class="text-sm text-gray-600 mt-2 italic bg-gray-50 p-2 rounded">
            "{{ $request->message }}"
        </p>
    @endif
    
    {{-- === TOMBOL MODERN === --}}
    <div class="mt-4 flex items-center gap-4">
        <form action="{{ route('peminjaman-requests.approve', $request) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fa-solid fa-check mr-2"></i> Setujui
            </button>
        </form>
        <button onclick="document.getElementById('rejectForm-{{ $request->id }}').classList.toggle('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <i class="fa-solid fa-times mr-2"></i> Tolak
        </button>
    </div>
    
    {{-- Form Penolakan (tidak berubah, hanya disembunyikan) --}}
    <form id="rejectForm-{{ $request->id }}" action="{{ route('peminjaman-requests.reject', $request) }}" method="POST" class="hidden mt-4 bg-gray-50 p-4 rounded-md">
        @csrf
        <label for="rejection_reason-{{$request->id}}" class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan (Wajib Diisi):</label>
        <textarea name="rejection_reason" id="rejection_reason-{{$request->id}}" rows="2" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
        <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Kirim Penolakan</button>
    </form>
</div>