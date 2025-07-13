<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Peminjaman Anggota') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Permintaan Tertunda</h3>
                    <p class="text-sm text-gray-600 mb-4">Berikut adalah daftar permintaan dari unit lain untuk meminjam anggota tim Anda.</p>

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @forelse ($pendingRequests as $request)
                            {{-- Kita tambahkan @if untuk memastikan semua data relasi ada --}}
                            @if ($request->requester && $request->requestedUser && $request->project)
                                <div class="border p-4 rounded-lg">
                                    <p>
                                        {{-- PERBAIKAN: Gunakan operator null-safe (?->) untuk keamanan --}}
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
                            @else
                                {{-- Tampilkan pesan ini jika ada data permintaan yang relasinya rusak --}}
                                <div class="border border-red-200 bg-red-50 p-4 rounded-lg text-sm text-red-700">
                                    Permintaan dengan ID #{{ $request->id }} tidak dapat ditampilkan karena data proyek atau pengguna terkait telah dihapus.
                                </div>
                            @endif
                        @empty
                            <p>Tidak ada permintaan tertunda saat ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>