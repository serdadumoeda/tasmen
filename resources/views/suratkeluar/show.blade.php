<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detail Surat Keluar') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Perihal: {{ $surat->perihal }}</p>
            </div>
            <a href="{{ route('surat-keluar.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Surat
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Kolom utama untuk konten surat --}}
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8">
                        @if ($surat->konten)
                            <div class="prose max-w-none">
                                {!! $surat->konten !!}
                            </div>
                        @elseif ($surat->lampiran->isNotEmpty())
                             @php $lampiran = $surat->lampiran->first(); @endphp
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Lampiran Surat</h3>
                            @if (Str::contains($lampiran->tipe_file, 'pdf'))
                                <iframe src="{{ route('lampiran.show', $lampiran) }}" class="w-full h-screen rounded-lg border"></iframe>
                            @else
                                <a href="{{ route('lampiran.show', $lampiran) }}" target="_blank" class="text-indigo-600 hover:underline">
                                    Lihat Lampiran: {{ $lampiran->nama_file }}
                                </a>
                            @endif
                        @else
                            <p class="text-gray-500">Tidak ada konten atau lampiran untuk ditampilkan.</p>
                        @endif
                    </div>
                </div>

                {{-- Kolom samping untuk info dan aksi --}}
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Surat</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Status:</span>
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($surat->status == 'draft') bg-yellow-100 text-yellow-800 @endif
                                    @if($surat->status == 'disetujui') bg-green-100 text-green-800 @endif
                                ">
                                    {{ ucfirst($surat->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Perihal:</span>
                                <span class="text-gray-800 text-right">{{ $surat->perihal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Tanggal Surat:</span>
                                <span class="text-gray-800">{{ $surat->tanggal_surat->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Dibuat oleh:</span>
                                <span class="text-gray-800">{{ $surat->pembuat->name }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($surat->status === 'disetujui' && $surat->final_pdf_path)
                        <div class="bg-white p-6 rounded-lg shadow-xl text-center">
                            <h3 class="text-lg font-bold text-gray-800 mb-2">Dokumen Final</h3>
                            <p class="text-sm text-gray-500 mb-4">Surat telah disetujui dan PDF final telah dibuat.</p>
                            <a href="{{ route('surat-keluar.download', $surat) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i> Unduh PDF Final
                            </a>
                        </div>
                    @elseif ($surat->status === 'draft')
                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Aksi Persetujuan</h3>
                            <form action="{{ route('surat-keluar.approve', $surat) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label for="penyetuju_id" class="block text-sm font-medium text-gray-700">Pilih Pejabat Penyetuju</label>
                                        <select id="penyetuju_id" name="penyetuju_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            {{-- Logic to get approvers would go here. For now, a placeholder. --}}
                                            <option value="{{ Auth::id() }}">Approve by Me ({{ Auth::user()->name }})</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="with_signature" id="with_signature" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="with_signature" class="ml-2 text-sm text-gray-600">Sertakan Tanda Tangan Visual</label>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        <i class="fas fa-check-circle mr-2"></i> Setujui & Finalisasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
