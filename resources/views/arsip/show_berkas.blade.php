<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('arsip.index') }}" class="text-indigo-600 hover:text-indigo-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Arsip
            </a>
            <span class="mx-2 text-gray-500">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Berkas: {{ $berkas->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold">{{ $berkas->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $berkas->description }}</p>
                        <p class="text-xs text-gray-500 mt-1">Dibuat pada: {{ $berkas->created_at->format('d M Y') }} | Jumlah Surat: {{ $suratList->total() }}</p>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('arsip.berkas.show', $berkas) }}" method="GET" class="mb-8 p-4 bg-gray-50 rounded-lg border">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                <label for="date_range" class="block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                                <input type="text" name="date_range" id="date_range" value="{{ request('date_range') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Pilih rentang tanggal...">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('arsip.berkas.show', $berkas) }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-xs hover:bg-gray-700">Reset</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Surat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($surat->klasifikasi)->kode }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if ($surat->file_path)
                                                <a href="{{ route('surat.download', $surat) }}" class="text-indigo-600 hover:text-indigo-900">Unduh</a>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada surat di dalam berkas ini.</td>
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
