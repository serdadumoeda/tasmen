<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detail & Disposisi Surat Masuk') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Perihal: {{ $surat->perihal }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('disposisi.lacak', $surat) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700">
                    <i class="fas fa-sitemap mr-2"></i> Lacak Disposisi
                </a>
                <a href="{{ route('surat.make-task', $surat) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700">
                    <i class="fas fa-tasks mr-2"></i> Jadikan Tugas
                </a>
                <a href="{{ route('surat-masuk.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Surat
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Kolom utama untuk konten surat (lampiran) --}}
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8">
                        @if($surat->lampiran->isNotEmpty())
                            @php $lampiran = $surat->lampiran->first(); @endphp
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Lampiran Surat</h3>
                            @if (Str::contains($lampiran->tipe_file, 'pdf'))
                                <iframe src="{{ route('lampiran.show', $lampiran) }}" class="w-full h-screen rounded-lg border"></iframe>
                            @elseif (Str::contains($lampiran->tipe_file, 'image'))
                                <img src="{{ route('lampiran.show', $lampiran) }}" alt="Lampiran" class="w-full h-auto rounded-lg border">
                            @else
                                <a href="{{ route('lampiran.show', $lampiran) }}" target="_blank" class="text-indigo-600 hover:underline">
                                    Lihat Lampiran: {{ $lampiran->nama_file }}
                                </a>
                            @endif
                        @else
                            <p class="text-gray-500">Tidak ada lampiran untuk surat ini.</p>
                        @endif
                    </div>
                </div>

                {{-- Kolom samping untuk info dan aksi --}}
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Surat</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Nomor Surat:</span>
                                <span class="text-gray-800 text-right w-2/3 font-mono">{{ $surat->nomor_surat }}</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Tanggal Surat:</span>
                                <span class="text-gray-800 text-right w-2/3">{{ $surat->tanggal_surat->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="font-semibold text-gray-600 w-1/3">Diarsipkan oleh:</span>
                                <span class="text-gray-800 text-right w-2/3">{{ $surat->pembuat->name }}</span>
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
</x-app-layout>

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
