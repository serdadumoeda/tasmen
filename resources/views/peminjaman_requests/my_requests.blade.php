<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Status & Persetujuan Penugasan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Bagian Persetujuan Tertunda --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-inbox mr-2 text-orange-600"></i> Persetujuan untuk Tim Saya
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">Permintaan dari unit lain yang menunggu keputusan Anda.</p>
                    
                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-6"> {{-- Spasi antar kartu --}}
                        @forelse ($myPendingApprovals as $request)
                            @if ($request->requester && $request->requestedUser && $request->project)
                                <div class="border border-gray-200 p-6 rounded-xl shadow-md bg-white hover:shadow-lg transition-all duration-200 ease-in-out transform hover:scale-[1.005]">
                                    <div class="flex items-center mb-3">
                                        <i class="fas fa-user-friends text-blue-600 text-2xl mr-4"></i>
                                        <div>
                                            <p class="text-base font-semibold text-gray-800">
                                                <span class="text-indigo-700">{{ $request->requester?->name ?? '[Pengguna Dihapus]' }}</span> meminta untuk menugaskan
                                                <span class="text-purple-700">{{ $request->requestedUser?->name ?? '[Pengguna Dihapus]' }}</span>
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1 flex items-center">
                                                <i class="fas fa-folder-open mr-2 text-gray-500"></i> Untuk kegiatan: <span class="font-medium text-gray-800">{{ $request->project?->name ?? '[Kegiatan Dihapus]' }}</span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                <i class="fas fa-clock mr-2 text-gray-400"></i> Diajukan: {{ $request->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    @if ($request->message)
                                        <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-3 rounded-r-lg italic shadow-sm">
                                            <p class="flex items-center"><i class="fas fa-quote-left mr-2 text-blue-500"></i> "{{ $request->message }}"</p>
                                        </div>
                                    @endif

                                    <div class="mt-5 flex items-center gap-4 border-t border-gray-200 pt-4">
                                        <form action="{{ route('peminjaman-requests.approve', $request) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                                <i class="fas fa-check-circle mr-2"></i> Setujui
                                            </button>
                                        </form>
                                        <button onclick="document.getElementById('rejectForm-{{ $request->id }}').classList.toggle('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                            <i class="fas fa-times-circle mr-2"></i> Tolak
                                        </button>
                                    </div>
                                    <form id="rejectForm-{{ $request->id }}" action="{{ route('peminjaman-requests.reject', $request) }}" method="POST" class="hidden mt-4 bg-gray-50 p-4 rounded-lg shadow-inner">
                                        @csrf
                                        <label for="rejection_reason-{{$request->id}}" class="block text-sm font-semibold text-gray-700 mb-2">Alasan Penolakan:</label>
                                        <textarea name="rejection_reason" id="rejection_reason-{{$request->id}}" rows="2" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-red-500 focus:ring-red-500 transition duration-150" required placeholder="Contoh: Tidak dapat dilepas karena beban kerja tinggi."></textarea>
                                        <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                            <i class="fas fa-paper-plane mr-2"></i> Kirim Penolakan
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="border border-red-300 bg-red-100 p-4 rounded-lg text-sm text-red-800 shadow-md flex items-center">
                                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                                    Permintaan dengan ID #{{ $request->id }} tidak dapat ditampilkan karena data kegiatan atau pengguna terkait telah dihapus.
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-gray-500 p-10 bg-gray-50 rounded-lg shadow-md">
                                <i class="fas fa-check-double fa-3x text-green-400 mb-4"></i>
                                <p class="text-lg">Tidak ada permintaan persetujuan yang tertunda saat ini.</p>
                                <p class="text-sm text-gray-400 mt-2">Semua permintaan telah ditinjau atau tidak ada permintaan baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Riwayat Persetujuan Saya (Diperbarui) --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-history mr-2 text-gray-600"></i> Riwayat Persetujuan Saya
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">Daftar keputusan yang telah Anda buat terhadap permintaan penugasan.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100"> {{-- Header tabel lebih menonjol --}}
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Peminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Anggota Diminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kegiatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dokumen</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status & Alasan</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100"> {{-- Divider lebih halus --}}
                                @forelse ($approvalHistory as $request)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150"> {{-- Hover effect pada baris --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                            <i class="fas fa-user-tag mr-2 text-gray-400"></i> {{ $request->requester?->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                            <i class="fas fa-user-check mr-2 text-gray-400"></i> {{ $request->requestedUser?->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                            <i class="fas fa-folder mr-2 text-gray-400"></i> {{ $request->project?->name ?? '[Kegiatan Dihapus]' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($request->surat)
                                                <a href="{{ route('surat-keluar.show', $request->surat) }}" class="text-indigo-600 hover:underline" title="Lihat Surat Permohonan">
                                                    <i class="fas fa-file-alt mr-1"></i>
                                                    {{ $request->surat->status == 'draft' ? 'Draf' : 'Final' }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($request->status == 'approved')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-900 shadow-sm"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                                            @elseif ($request->status == 'rejected')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-900 shadow-sm"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                                                @if($request->rejection_reason)
                                                    <p class="text-xs text-gray-600 mt-1 italic pl-1"><i class="fas fa-info-circle mr-1"></i> "{{ Str::limit($request->rejection_reason, 40) }}"</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('peminjaman-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus riwayat ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-full text-gray-500 hover:bg-red-100 hover:text-red-600 transition-colors duration-200" title="Hapus Riwayat">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 text-lg">Tidak ada riwayat persetujuan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6"> {{-- Margin atas paginasi --}}
                        {{ $approvalHistory->links() }}
                    </div>
                </div>
            </div>

            {{-- Riwayat Permintaan Saya (Diperbaiki) --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-paper-plane mr-2 text-blue-600"></i> Riwayat Permintaan Saya
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">Daftar permintaan yang telah Anda ajukan untuk menugaskan anggota.</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Anggota Diminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kegiatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Approver</th>
                                     <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dokumen</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status & Alasan</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($mySentRequests as $request)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                            <i class="fas fa-user-tag mr-2 text-gray-400"></i> {{ $request->requestedUser?->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                            <i class="fas fa-folder mr-2 text-gray-400"></i> {{ $request->project?->name ?? '[Kegiatan Dihapus]' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                            <i class="fas fa-user-shield mr-2 text-gray-400"></i> {{ $request->approver?->name }}
                                        </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($request->surat)
                                                <a href="{{ route('surat-keluar.show', $request->surat) }}" class="text-indigo-600 hover:underline" title="Lihat Surat Permohonan">
                                                    <i class="fas fa-file-alt mr-1"></i>
                                                    {{ $request->surat->status == 'draft' ? 'Draf' : 'Final' }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             @if ($request->status == 'pending')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-200 text-yellow-900 shadow-sm"><i class="fas fa-hourglass-half mr-1"></i> Menunggu</span>
                                            @elseif ($request->status == 'approved')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-900 shadow-sm"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                                            @elseif ($request->status == 'rejected')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-900 shadow-sm"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                                                @if($request->rejection_reason)
                                                    <p class="text-xs text-gray-600 mt-1 italic pl-1"><i class="fas fa-info-circle mr-1"></i> "{{ Str::limit($request->rejection_reason, 40) }}"</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('peminjaman-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus riwayat ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-full text-gray-500 hover:bg-red-100 hover:text-red-600 transition-colors duration-200" title="Hapus Riwayat">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 text-lg">Anda belum pernah mengajukan permintaan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $mySentRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>