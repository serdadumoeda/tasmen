<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Penugasan Anggota') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-handshake-angle mr-2 text-indigo-600"></i> Permintaan Tertunda
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">Berikut adalah daftar permintaan dari unit lain untuk menugaskan anggota tim Anda.</p>

                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert"> {{-- Styling alert konsisten --}}
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-6"> {{-- Spasi antar kartu --}}
                        @forelse ($pendingRequests as $request)
                            @if ($request->requester && $request->requestedUser && $request->project)
                                <div class="border border-gray-200 p-6 rounded-xl shadow-md bg-white hover:shadow-lg transition-all duration-200 ease-in-out transform hover:scale-[1.005]"> {{-- Kartu permintaan lebih menonjol --}}
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
                                        <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-3 rounded-r-lg italic shadow-sm"> {{-- Styling pesan lebih menonjol --}}
                                            <p class="flex items-center"><i class="fas fa-quote-left mr-2 text-blue-500"></i> "{{ $request->message }}"</p>
                                        </div>
                                    @endif

                                    <div class="mt-5 flex items-center gap-4 border-t border-gray-200 pt-4"> {{-- Tombol aksi --}}
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
                                    <form id="rejectForm-{{ $request->id }}" action="{{ route('peminjaman-requests.reject', $request) }}" method="POST" class="hidden mt-4 bg-gray-50 p-4 rounded-lg shadow-inner"> {{-- Form penolakan --}}
                                        @csrf
                                        <label for="rejection_reason-{{$request->id}}" class="block text-sm font-semibold text-gray-700 mb-2">Alasan Penolakan:</label>
                                        <textarea name="rejection_reason" id="rejection_reason-{{$request->id}}" rows="2" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-red-500 focus:ring-red-500 transition duration-150" required placeholder="Contoh: Tidak dapat dilepas karena beban kerja tinggi."></textarea>
                                        <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                            <i class="fas fa-paper-plane mr-2"></i> Kirim Penolakan
                                        </button>
                                    </form>
                                </div>
                            @else
                                {{-- Tampilkan pesan ini jika ada data permintaan yang relasinya rusak --}}
                                <div class="border border-red-300 bg-red-100 p-4 rounded-lg text-sm text-red-800 shadow-md flex items-center">
                                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                                    Permintaan dengan ID #{{ $request->id }} tidak dapat ditampilkan karena data kegiatan atau pengguna terkait telah dihapus.
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-gray-500 p-10 bg-gray-50 rounded-lg shadow-md">
                                <i class="fas fa-check-double fa-3x text-green-400 mb-4"></i>
                                <p class="text-lg">Tidak ada permintaan tertunda saat ini.</p>
                                <p class="text-sm text-gray-400 mt-2">Semua permintaan telah ditinjau atau tidak ada permintaan baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>