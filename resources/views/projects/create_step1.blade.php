<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Kegiatan Baru (Langkah 1 dari 2): Informasi Kegiatan') }}
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    {{-- Mengubah py-12 menjadi py-8 untuk konsistensi padding vertikal --}}
    <div class="py-8 bg-gray-50"> 
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            {{-- Mengubah shadow-sm menjadi shadow-xl dan memastikan rounded-lg --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('projects.store.step1') }}" method="POST" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                        @csrf
                        @if(isset($selected_surat_id))
                            <input type="hidden" name="surat_id_from_flow" value="{{ $selected_surat_id }}">
                        @endif
                        
                        @if ($errors->any())
                            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <i class="fas fa-exclamation-triangle h-5 w-5 text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <strong class="font-bold">Oops! Ada yang salah:</strong>
                                        <ul class="mt-1.5 list-disc list-inside text-sm">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-6"> {{-- Menggunakan space-y-6 untuk konsistensi antar field --}}
                            <div>
                                <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-file-signature mr-2 text-gray-500"></i> Nama Kegiatan <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('name', $project->name) }}" required autofocus>
                            </div>
                            
                            <div>
                                <label for="description" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-align-left mr-2 text-gray-500"></i> Deskripsi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>{{ old('description', $project->description) }}</textarea>
                            </div>

                            <div>
                                <label for="surat_ids" class="block font-semibold text-sm text-gray-700 mb-1">
                                    <i class="fas fa-gavel mr-2 text-gray-500"></i> Dasar Surat (Opsional)
                                </label>
                                <select name="surat_ids[]" id="surat_ids" multiple class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                                    @foreach($suratList as $surat)
                                        <option value="{{ $surat->id }}" @selected(in_array($surat->id, old('surat_ids', [$selected_surat_id ?? ''])))>
                                            {{ $surat->nomor_surat ?? 'No. Belum Ada' }} - {{ $surat->perihal }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Pilih satu atau lebih surat sebagai dasar hukum kegiatan. Tahan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- Mengubah gap-4 menjadi gap-6 --}}
                                <div>
                                    <label for="start_date" class="block font-semibold text-sm text-gray-700 mb-1">
                                        <i class="fas fa-calendar-alt mr-2 text-gray-500"></i> Tanggal Mulai
                                    </label>
                                    <input type="date" name="start_date" id="start_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('start_date') }}">
                                </div>
                                <div>
                                    <label for="end_date" class="block font-semibold text-sm text-gray-700 mb-1">
                                        <i class="fas fa-calendar-check mr-2 text-gray-500"></i> Tanggal Selesai
                                    </label>
                                    <input type="date" name="end_date" id="end_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('end_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105 disabled:opacity-50"
                                    :disabled="isSubmitting">
                                <span x-show="!isSubmitting" class="inline-flex items-center">
                                    Lanjut ke Penugasan Tim <i class="fas fa-arrow-right ml-2"></i>
                                </span>
                                <span x-show="isSubmitting" class="inline-flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>