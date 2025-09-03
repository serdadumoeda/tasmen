@props(['items'])

<div class="bg-white p-6 rounded-xl shadow-xl">
    <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-3">
        <i class="fas fa-inbox mr-2 text-indigo-600"></i> Kotak Masuk Persetujuan
    </h3>
    <div class="space-y-4">
        @forelse ($items as $item)
            <div class="p-4 rounded-lg hover:bg-gray-50 transition duration-200 border border-gray-200">
                @if ($item instanceof \App\Models\LeaveRequest)
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                <span class="font-bold text-blue-600">Permintaan Cuti:</span> {{ $item->user->name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Jenis: {{ $item->leaveType->name }} | Durasi: {{ $item->duration_days }} hari
                            </p>
                        </div>
                        <a href="{{ route('leaves.show', $item) }}" class="text-xs inline-block px-3 py-1 font-semibold text-indigo-800 bg-indigo-100 rounded-full hover:bg-indigo-200">
                            Lihat Detail
                        </a>
                    </div>
                @elseif ($item instanceof \App\Models\Surat)
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                <span class="font-bold text-green-600">Persetujuan Surat Keluar:</span> {{ $item->perihal }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Dibuat oleh: {{ $item->pembuat->name }}
                            </p>
                        </div>
                        <a href="{{ route('surat-keluar.show', $item) }}" class="text-xs inline-block px-3 py-1 font-semibold text-indigo-800 bg-indigo-100 rounded-full hover:bg-indigo-200">
                            Lihat Detail
                        </a>
                    </div>
                @elseif ($item instanceof \App\Models\PeminjamanRequest)
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                <span class="font-bold text-purple-600">Peminjaman Pegawai:</span> {{ $item->requestedUser->name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Diminta oleh: {{ $item->requester->name }}
                            </p>
                        </div>
                        <a href="{{ route('peminjaman-requests.index') }}" class="text-xs inline-block px-3 py-1 font-semibold text-indigo-800 bg-indigo-100 rounded-full hover:bg-indigo-200">
                            Lihat Detail
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                <p class="text-gray-600 font-semibold">Inbox Anda bersih!</p>
                <p class="text-sm text-gray-500">Tidak ada item yang memerlukan persetujuan Anda saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
