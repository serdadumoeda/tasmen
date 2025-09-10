<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Arsip Digital Persuratan') }}
            </h2>
            <x-secondary-button :href="route('arsip.workflow')">
                <i class="fas fa-sitemap mr-2"></i>
                Lihat Alur Kerja
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                {{-- Sidebar for Berkas Management --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h3 class="font-bold text-lg mb-4">Buat Berkas Baru</h3>
                        <form action="{{ route('arsip.berkas.store') }}" method="POST">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Berkas</label>
                                    <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                    <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    <i class="fas fa-plus-circle mr-1"></i> Buat
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h3 class="font-bold text-lg mb-4">Daftar Berkas Virtual</h3>
                        <ul class="space-y-2">
                            @forelse($berkasList as $berkas)
                                <li class="flex items-center justify-between p-2 rounded-md hover:bg-gray-100">
                                    <a href="#" class="flex items-center text-sm text-gray-700 hover:text-indigo-600">
                                        <i class="fas fa-folder text-yellow-500 mr-3"></i>
                                        <span>{{ $berkas->name }} ({{ $berkas->surat()->count() }})</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">Belum ada berkas.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Main Content for Surat List --}}
                <div class="lg:col-span-3 bg-white shadow-sm sm:rounded-lg">
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
                                <label for="date_range" class="block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                                <input type="text" name="date_range" id="date_range" value="{{ request('date_range') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Pilih rentang tanggal...">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="{{ route('arsip.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-xs hover:bg-gray-700">Reset</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                        </div>
                    </form>

                    {{-- Form for Filing Letters --}}
                    <form action="{{ route('arsip.berkas.add-surat') }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><input type="checkbox" id="select-all"></th>
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
                                        <td class="px-4 py-4 whitespace-nowrap"><input type="checkbox" name="surat_ids[]" value="{{ $surat->id }}" class="rounded border-gray-300 shadow-sm surat-checkbox"></td>
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

                        <div class="mt-4 p-2 border-t flex items-center space-x-4 bg-gray-50 rounded-b-lg">
                            <label for="berkas_id" class="text-sm font-medium">Pilih Berkas:</label>
                            <select name="berkas_id" id="berkas_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">-- Tujuan Berkas --</option>
                                @foreach($berkasList as $berkas)
                                    <option value="{{ $berkas->id }}">{{ $berkas->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">
                                <i class="fas fa-folder-plus mr-1"></i> Masukkan ke Berkas
                            </button>
                        </div>
                    </form>

                    <div class="mt-6">
                        {{ $suratList->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.getElementById('select-all').addEventListener('change', function(event) {
                document.querySelectorAll('.surat-checkbox').forEach(function(checkbox) {
                    checkbox.checked = event.target.checked;
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d M Y",
                });
            });
        </script>
    @endpush
</x-app-layout>
