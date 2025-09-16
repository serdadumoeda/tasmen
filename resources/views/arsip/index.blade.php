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
                                <li class="flex items-center justify-between p-2 rounded-md hover:bg-gray-100 group">
                                    <a href="{{ route('arsip.berkas.show', $berkas) }}" class="flex items-center text-sm text-gray-700 hover:text-indigo-600 flex-grow">
                                        <i class="fas fa-folder text-yellow-500 mr-3"></i>
                                        <span class="flex-grow">{{ $berkas->name }} ({{ $berkas->surat_count ?? $berkas->surat->count() }})</span>
                                    </a>
                                    <div class="relative opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="event.stopPropagation(); this.nextElementSibling.classList.toggle('hidden');" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden">
                                            <a href="#" onclick="event.preventDefault(); openEditModal({{ json_encode($berkas) }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                                            <form action="{{ route('arsip.berkas.destroy', $berkas) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas ini? Semua surat di dalamnya akan dikeluarkan dari berkas.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">Belum ada berkas.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Main Content for Surat List --}}
                <div class="lg:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{-- Filter Form --}}
                        <form action="{{ route('arsip.index') }}" method="GET" class="mb-8 p-4 bg-gray-50 rounded-lg border">
                             <h3 class="font-bold text-lg mb-4">Pencarian & Filter Surat</h3>
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
                    <form action="{{ route('arsip.berkas.move-surat') }}" method="POST">
                        @csrf

                        {{-- Validation Errors --}}
                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                                <strong class="font-bold">Oops! Ada yang salah:</strong>
                                <ul class="mt-1 list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Surat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasifikasi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Arsip</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($suratList as $surat)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $surat->nomor_surat ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $surat->perihal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $surat->tanggal_surat->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($surat->klasifikasi)->kode }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($surat->berkas)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-folder-open mr-1.5"></i>
                                                    {{ $surat->berkas->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic">Belum Diarsipkan</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button onclick="openMoveModal({{ json_encode($surat) }})" class="text-green-600 hover:text-green-900">Pindahkan</button>
                                            @if ($surat->file_path)
                                                <a href="{{ route('surat.download', $surat) }}" class="text-indigo-600 hover:text-indigo-900">Unduh</a>
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

    <!-- Edit Berkas Modal -->
    <div id="editBerkasModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editBerkasForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Berkas</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="edit_name" class="block text-sm font-medium text-gray-700">Nama Berkas</label>
                                <input type="text" name="name" id="edit_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="edit_description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea name="description" id="edit_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Move Surat Modal -->
    <div id="moveSuratModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="moveSuratForm" action="{{ route('arsip.berkas.move-surat') }}" method="POST">
                    @csrf
                    <input type="hidden" name="surat_ids[]" id="move_surat_id">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Pindahkan Surat</h3>
                        <p class="text-sm text-gray-600 mt-2" id="move_surat_perihal"></p>
                        <div class="mt-4">
                            <label for="move_berkas_id" class="block text-sm font-medium text-gray-700">Pilih Berkas Tujuan</label>
                            <select name="berkas_id" id="move_berkas_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Pilih Berkas --</option>
                                @foreach($berkasList as $berkas)
                                    <option value="{{ $berkas->id }}">{{ $berkas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">Pindahkan</button>
                        <button type="button" onclick="closeMoveModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function openEditModal(berkas) {
                const modal = document.getElementById('editBerkasModal');
                const form = document.getElementById('editBerkasForm');
                form.action = `/arsip/berkas/${berkas.id}`;
                document.getElementById('edit_name').value = berkas.name;
                document.getElementById('edit_description').value = berkas.description;
                modal.classList.remove('hidden');
            }

            function closeEditModal() {
                const modal = document.getElementById('editBerkasModal');
                modal.classList.add('hidden');
            }

            function openMoveModal(surat) {
                const modal = document.getElementById('moveSuratModal');
                document.getElementById('move_surat_id').value = surat.id;
                document.getElementById('move_surat_perihal').textContent = `Anda akan memindahkan surat dengan perihal: "${surat.perihal}"`;
                modal.classList.remove('hidden');
            }

            function closeMoveModal() {
                const modal = document.getElementById('moveSuratModal');
                modal.classList.add('hidden');
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Date range picker logic
                const dateRange = document.getElementById('date_range');
                if(dateRange) {
                    flatpickr(dateRange, {
                        mode: "range",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "d M Y",
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
