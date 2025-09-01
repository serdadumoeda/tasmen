<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Arsip Digital Persuratan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Filter Form --}}
                    <form action="{{ route('arsip.index') }}" method="GET" class="mb-8 p-4 bg-gray-50 rounded-lg border">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div>
                                <label for="keyword" class="block text-sm font-medium text-gray-700">Kata Kunci</label>
                                <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Perihal atau Nomor Surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="klasifikasi_id" class="block text-sm font-medium text-gray-700">Klasifikasi</label>
                                <select name="klasifikasi_id" id="klasifikasi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Semua Klasifikasi</option>
                                    @foreach ($klasifikasi as $item)
                                        <option value="{{ $item->id }}" @selected(request('klasifikasi_id') == $item->id)>
                                            {{ $item->kode }} - {{ $item->deskripsi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="jenis" class="block text-sm font-medium text-gray-700">Jenis Surat</label>
                                <select name="jenis" id="jenis" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Semua Jenis</option>
                                    <option value="masuk" @selected(request('jenis') == 'masuk')>Masuk</option>
                                    <option value="keluar" @selected(request('jenis') == 'keluar')>Keluar</option>
                                </select>
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                                <div class="flex items-center space-x-2 mt-1">
                                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="block w-full rounded-md border-gray-300 shadow-sm">
                                    <span>-</span>
                                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('arsip.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-xs hover:bg-gray-700">Reset</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                        </div>
                    </form>

                    {{-- Results Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Surat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasifikasi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($suratList as $surat)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $surat->nomor_surat }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $surat->perihal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $surat->tanggal_surat->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $surat->jenis == 'masuk' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ ucfirst($surat->jenis) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($surat->klasifikasi)->kode }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if ($surat->final_pdf_path && Storage::disk('public')->exists($surat->final_pdf_path))
                                                 <a href="{{ route('surat-keluar.download', $surat) }}" class="text-indigo-600 hover:text-indigo-900">Unduh</a>
                                            @else
                                                 <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">Tidak ada surat yang cocok dengan kriteria pencarian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $suratList->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
