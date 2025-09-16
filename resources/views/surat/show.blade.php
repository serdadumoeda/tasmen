<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detail Surat') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Perihal: {{ $surat->perihal }}</p>
            </div>
            <div class="flex items-center space-x-2">
                @if($surat->berkas_id)
                    <button onclick="openArchiveModal()" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg font-semibold text-sm hover:bg-green-600">
                        <i class="fas fa-exchange-alt mr-2"></i> Pindahkan
                    </button>
                @else
                    <button onclick="openArchiveModal()" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg font-semibold text-sm hover:bg-yellow-600">
                        <i class="fas fa-archive mr-2"></i> Arsipkan
                    </button>
                @endif
                <a href="{{ route('disposisi.lacak', $surat) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700">
                    <i class="fas fa-sitemap mr-2"></i> Lacak Disposisi
                </a>
                <form action="{{ route('surat.make-task', $surat) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700">
                        <i class="fas fa-tasks mr-2"></i> Jadikan Tugas
                    </button>
                </form>
                <a href="{{ route('surat.make-project', $surat) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold text-sm hover:bg-purple-700">
                    <i class="fas fa-folder-plus mr-2"></i> Jadikan Kegiatan
                </a>
                <a href="{{ route('surat.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Kolom utama untuk konten surat --}}
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Dokumen Surat</h3>
                        @if($surat->file_path)
                            <div class="p-6 border rounded-lg bg-gray-50 text-center">
                                <i class="fas fa-file-alt fa-3x text-gray-400 mb-4"></i>
                                <h4 class="font-semibold text-gray-700">Dokumen terlampir.</h4>
                                <p class="text-sm text-gray-500 mb-4">Klik tombol di bawah untuk mengunduh dan melihat dokumen.</p>
                                <a href="{{ route('surat.download', $surat) }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700">
                                    <i class="fas fa-download mr-2"></i>
                                    Download Dokumen
                                </a>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-10">Tidak ada dokumen yang diunggah untuk surat ini.</p>
                        @endif
                    </div>
                </div>

                {{-- Kolom samping untuk info dan aksi --}}
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Surat</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Perihal:</span>
                                <span class="text-gray-800 text-right w-2/3">{{ $surat->perihal }}</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Tanggal Surat:</span>
                                <span class="text-gray-800 text-right w-2/3">{{ $surat->tanggal_surat->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Diunggah oleh:</span>
                                <span class="text-gray-800 text-right w-2/3">{{ $surat->pembuat->name }}</span>
                            </div>
                             <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Status:</span>
                                <span @class([
                                    'px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full',
                                    'bg-blue-100 text-blue-800' => $surat->status === 'draft',
                                    'bg-yellow-100 text-yellow-800' => $surat->status === 'dikirim',
                                    'bg-green-100 text-green-800' => $surat->status === 'disetujui',
                                    'bg-red-100 text-red-800' => $surat->status === 'ditolak',
                                    'bg-purple-100 text-purple-800' => $surat->status === 'perlu_revisi',
                                    'bg-gray-100 text-gray-800' => $surat->status === 'diarsipkan',
                                ])>
                                    {{ ucfirst(str_replace('_', ' ', $surat->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Form Disposisi --}}
                    @if ($parentDisposisi || Auth::user()->can('create', App\Models\Disposisi::class))
                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Buat Disposisi</h3>
                            <form action="{{ route('disposisi.store', $surat) }}" method="POST">
                                @csrf
                                @if($parentDisposisi)
                                    <input type="hidden" name="parent_disposisi_id" value="{{ $parentDisposisi->id }}">
                                @endif
                                <div class="space-y-4">
                                    <div>
                                        <label for="penerima_id" class="block text-sm font-medium text-gray-700">Disposisikan Kepada (Tujuan Utama)</label>
                                        <select id="penerima_id" name="penerima_id[]" multiple class="mt-1 block w-full rounded-md tom-select">
                                            @foreach ($dispositionUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tembusan_id" class="block text-sm font-medium text-gray-700">Tembusan (CC)</label>
                                        <select id="tembusan_id" name="tembusan_id[]" multiple class="mt-1 block w-full rounded-md tom-select">
                                            @foreach ($dispositionUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="instruksi" class="block text-sm font-medium text-gray-700">Instruksi / Catatan</label>
                                        <textarea id="instruksi" name="instruksi" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm"></textarea>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                        <i class="fas fa-paper-plane mr-2"></i> Kirim Disposisi
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- Riwayat Disposisi --}}
                    <div class="bg-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Riwayat Disposisi</h3>
                        <ul class="space-y-2">
                            @forelse ($topLevelDisposisi as $item)
                                <x-disposisi-item :item="$item" />
                            @empty
                                <li class="text-sm text-gray-500">Belum ada riwayat disposisi.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Modal -->
    <div id="archiveModal" class="fixed z-20 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="archiveForm" action="{{ route('arsip.berkas.move-surat') }}" method="POST">
                    @csrf
                    <input type="hidden" name="surat_ids[]" value="{{ $surat->id }}">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="archiveModalTitle">Arsipkan Surat</h3>
                        <p class="text-sm text-gray-600 mt-2" id="archiveModalText">Pilih berkas virtual untuk mengarsipkan surat dengan perihal: "{{ $surat->perihal }}"</p>
                        <div class="mt-4">
                            <label for="berkas_id" class="block text-sm font-medium text-gray-700">Pilih Berkas</label>
                            <select name="berkas_id" id="berkas_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Pilih Berkas --</option>
                                @foreach($berkasList as $berkas)
                                    <option value="{{ $berkas->id }}">{{ $berkas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 sm:ml-3 sm:w-auto sm:text-sm">Arsipkan</button>
                        <button type="button" onclick="closeArchiveModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="styles">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
        <style>
            .ts-control {
                border-radius: 0.5rem;
                border-color: #d1d5db;
                padding: 0.5rem 0.75rem;
            }
            .ts-control .item {
                background-color: #4f46e5;
                color: white;
                border-radius: 0.25rem;
                font-weight: 500;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem;
            }
        </style>
    </x-slot>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function openArchiveModal() {
            const isArchived = {{ $surat->berkas_id ? 'true' : 'false' }};
            const modal = document.getElementById('archiveModal');
            const title = document.getElementById('archiveModalTitle');
            const text = document.getElementById('archiveModalText');
            const perihal = "{{ addslashes($surat->perihal) }}";

            if (isArchived) {
                title.textContent = 'Pindahkan Surat';
                text.textContent = `Pilih berkas tujuan baru untuk surat dengan perihal: "${perihal}"`;
            } else {
                title.textContent = 'Arsipkan Surat';
                text.textContent = `Pilih berkas virtual untuk mengarsipkan surat dengan perihal: "${perihal}"`;
            }

            modal.classList.remove('hidden');
        }

        function closeArchiveModal() {
            document.getElementById('archiveModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('penerima_id')) {
                new TomSelect('#penerima_id',{
                    plugins: ['remove_button'],
                    create: false,
                    placeholder: 'Pilih satu atau lebih tujuan...'
                });
            }
            if (document.getElementById('tembusan_id')) {
                new TomSelect('#tembusan_id',{
                    plugins: ['remove_button'],
                    create: false,
                    placeholder: 'Pilih satu atau lebih tembusan...'
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
