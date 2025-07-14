<div class="border p-4 rounded-lg shadow-sm">
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
    <div class="mt-4 flex items-center gap-4">
        <form action="{{ route('peminjaman-requests.approve', $request) }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded-md hover:bg-green-600">Setujui</button>
        </form>
        <button onclick="document.getElementById('rejectForm-{{ $request->id }}').classList.toggle('hidden')" class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-md hover:bg-red-600">Tolak</button>
    </div>
    <form id="rejectForm-{{ $request->id }}" action="{{ route('peminjaman-requests.reject', $request) }}" method="POST" class="hidden mt-4">
        @csrf
        <label for="rejection_reason-{{$request->id}}" class="block text-sm font-medium text-gray-700">Alasan Penolakan:</label>
        <textarea name="rejection_reason" id="rejection_reason-{{$request->id}}" rows="2" class="mt-1 block w-full rounded-md shadow-sm border-gray-300" required></textarea>
        <button type="submit" class="mt-2 px-4 py-2 bg-gray-700 text-white text-sm font-semibold rounded-md hover:bg-gray-800">Kirim Penolakan</button>
    </form>
</div>