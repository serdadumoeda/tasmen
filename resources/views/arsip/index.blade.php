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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main content for Berkas list --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">Daftar Berkas Virtual Anda</h3>
                            <button @click="showCreateBerkasModal = true" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                <i class="fas fa-plus-circle mr-1"></i> Buat Berkas Baru
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($berkasList as $berkas)
                                <a href="{{ route('arsip.berkas.show', $berkas) }}" class="block p-4 rounded-lg hover:bg-gray-100 border border-gray-200 hover:shadow-lg transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-folder fa-2x text-yellow-500 mr-4"></i>
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $berkas->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $berkas->surat()->count() }} surat</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="md:col-span-3 text-center py-8 text-gray-500">
                                    <p>Anda belum memiliki berkas. Buat satu untuk mulai mengarsipkan surat.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Sidebar for un-archived letters --}}
                <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-4 border-b pb-2">Surat Belum Diarsipkan</h3>

                        {{-- Form for Filing Letters --}}
                        <form action="{{ route('arsip.berkas.add-surat') }}" method="POST">
                            @csrf
                            @if($errors->any())
                                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                                    @foreach($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <div class="space-y-2 max-h-96 overflow-y-auto border rounded-md p-2">
                                @forelse ($suratList as $surat)
                                    <div class="flex items-center space-x-3 p-2 rounded hover:bg-gray-50">
                                        <input type="checkbox" name="surat_ids[]" value="{{ $surat->id }}" class="rounded border-gray-300 shadow-sm surat-checkbox">
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-800">{{ $surat->perihal }}</p>
                                            <p class="text-xs text-gray-500">{{ $surat->nomor_surat ?? 'No. Belum Ada' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 p-4 text-center">Semua surat sudah diarsipkan.</p>
                                @endforelse
                            </div>

                            @if($suratList->isNotEmpty())
                                <div class="mt-4 p-2 border-t flex items-center space-x-4">
                                    <label for="berkas_id_sidebar" class="text-sm font-medium">Arsipkan ke:</label>
                                    <select name="berkas_id" id="berkas_id_sidebar" class="flex-grow rounded-md border-gray-300 shadow-sm text-sm" required>
                                        <option value="">-- Pilih Berkas --</option>
                                        @foreach($berkasList as $berkas)
                                            <option value="{{ $berkas->id }}">{{ $berkas->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">
                                        <i class="fas fa-folder-plus mr-1"></i> Simpan
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Creating Berkas --}}
    <div x-data="{ showCreateBerkasModal: false }" @keydown.escape.window="showCreateBerkasModal = false" x-cloak>
        <div x-show="showCreateBerkasModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div @click.away="showCreateBerkasModal = false" class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                <h3 class="font-bold text-lg mb-4">Buat Berkas Baru</h3>
                <form action="{{ route('arsip.berkas.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="modal_name" class="block text-sm font-medium text-gray-700">Nama Berkas</label>
                            <input type="text" name="name" id="modal_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="modal_description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description" id="modal_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <x-secondary-button type="button" @click="showCreateBerkasModal = false">Batal</x-secondary-button>
                            <x-primary-button type="submit">Buat</x-primary-button>
                        </div>
                    </div>
                </form>
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
