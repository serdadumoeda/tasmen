<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Status & Persetujuan Peminjaman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Bagian Persetujuan Tertunda --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Persetujuan untuk Tim Saya</h3>
                    <p class="text-sm text-gray-600 mb-4">Permintaan dari unit lain yang menunggu keputusan Anda.</p>
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="space-y-4">
                        @forelse ($myPendingApprovals as $request)
                            {{-- Memuat kartu persetujuan yang tombolnya akan kita modernisasi --}}
                            @include('peminjaman_requests.partials.approval_card', ['request' => $request])
                        @empty
                            <p class="text-gray-500">✅ Tidak ada permintaan persetujuan yang tertunda.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Riwayat Persetujuan Saya (Diperbarui) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Riwayat Persetujuan Saya</h3>
                    <p class="text-sm text-gray-600 mb-4">Daftar keputusan yang telah Anda buat terhadap permintaan peminjaman.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota Diminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyek</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status & Alasan</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($approvalHistory as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->requester?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->requestedUser?->name }}</td>
                                        {{-- === TAMBAHAN NAMA PROYEK === --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $request->project?->name ?? '[Proyek Dihapus]' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($request->status == 'approved')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                            @elseif ($request->status == 'rejected')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                                {{-- === TAMBAHAN ALASAN PENOLAKAN === --}}
                                                @if($request->rejection_reason)
                                                    <p class="text-xs text-gray-500 mt-1 italic" title="Alasan Penolakan">"{{ Str::limit($request->rejection_reason, 40) }}"</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{-- === TOMBOL HAPUS MODERN === --}}
                                            <form action="{{ route('peminjaman-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus riwayat ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-full text-gray-400 hover:bg-red-100 hover:text-red-600 transition-colors duration-200" title="Hapus Riwayat">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada riwayat persetujuan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $approvalHistory->links() }}</div>
                </div>
            </div>

            {{-- Riwayat Permintaan Saya (Diperbaiki) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Riwayat Permintaan Saya</h3>
                    <p class="text-sm text-gray-600 mb-4">Daftar permintaan yang telah Anda ajukan untuk meminjam anggota.</p>
                    <div class="overflow-x-auto">
                        {{-- Tabel Riwayat Permintaan Saya --}}
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota Diminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyek</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status & Alasan</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($mySentRequests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->requestedUser?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $request->project?->name ?? '[Proyek Dihapus]' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->approver?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             @if ($request->status == 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                            @elseif ($request->status == 'approved')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                            @elseif ($request->status == 'rejected')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                                @if($request->rejection_reason)
                                                    <p class="text-xs text-gray-500 mt-1 italic" title="Alasan Penolakan">"{{ Str::limit($request->rejection_reason, 40) }}"</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('peminjaman-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus riwayat ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-full text-gray-400 hover:bg-red-100 hover:text-red-600 transition-colors duration-200" title="Hapus Riwayat">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Anda belum pernah mengajukan permintaan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $mySentRequests->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>