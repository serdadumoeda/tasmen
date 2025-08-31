<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Surat Keluar - Langkah 2: Isi Detail Surat') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900">
                    <form action="{{ route('surat-keluar.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                        <input type="hidden" name="submission_type" value="template">

                        <div class="space-y-6">
                            <div>
                                <label for="perihal" class="block font-semibold text-sm text-gray-700 mb-1">Perihal Surat <span class="text-red-500">*</span></label>
                                <input type="text" name="perihal" id="perihal" value="{{ old('perihal') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('perihal') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="tanggal_surat" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Surat <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_surat" id="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('tanggal_surat') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div id="placeholder-inputs" class="space-y-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                                <!-- Input fields for placeholders will be inserted here by JavaScript -->
                                <p id="no-placeholder-message" class="text-sm text-gray-600">Tidak ada placeholder dinamis yang ditemukan di template ini.</p>
                            </div>

                            <div>
                                <label class="block font-semibold text-sm text-gray-700 mb-1">Konten Template (Pratinjau)</label>
                                <div id="template-content-container" class="prose max-w-none p-4 border border-gray-300 rounded-lg bg-gray-100">
                                    {{-- Content will be injected by JavaScript to prevent Blade parsing --}}
                                </div>
                                <textarea name="konten_final" id="konten_final" class="hidden"></textarea>
                            </div>

                            <div class="mt-6 pt-6 border-t border-dashed">
                                <h3 class="font-semibold text-lg text-gray-800">Pratinjau Blok Penandatangan</h3>
                                <p class="text-sm text-gray-600 mb-4">Blok ini akan ditambahkan secara otomatis ke bagian bawah surat saat disetujui. Nama penandatangan akan sesuai dengan pejabat yang menyetujui.</p>
                                <div class="p-4 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-800">
                                    <div class="w-2/5 ml-auto text-center">
                                        <p>{{ $settings['signer_block_line_1'] ?? '' }}</p>
                                        <p>{{ $settings['signer_block_line_2'] ?? '' }}</p>
                                        <div class="h-20 my-2 flex items-center justify-center border-2 border-dashed rounded-md bg-gray-100">
                                            <span class="text-gray-500 text-xs italic">(Tanda Tangan & QR Code)</span>
                                        </div>
                                        <p class="font-bold">[Nama Penandatangan]</p>
                                        <p>{{ $settings['signer_block_line_3'] ?? '' }}</p>
                                        <p>{{ $settings['signer_block_line_4'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('surat-keluar.create') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Kembali Pilih Template</a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <i class="fas fa-save mr-2"></i> Simpan Draf Surat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const templateContent = @json($template->konten);
            const previewContainer = document.getElementById('template-content-container');
            const placeholderContainer = document.getElementById('placeholder-inputs');
            const noPlaceholderMessage = document.getElementById('no-placeholder-message');
            const finalContentTextarea = document.getElementById('konten_final');

            // Safely inject content into preview and hidden textarea
            previewContainer.innerHTML = templateContent;
            finalContentTextarea.value = templateContent;

            // Regex to find placeholders like {placeholder_name}
            const placeholderRegex = /\{\{([a-zA-Z0-9_]+)\}\}/g;
            let placeholders = new Set(); // Use a Set to store unique placeholders
            let match;

            while ((match = placeholderRegex.exec(templateContent)) !== null) {
                placeholders.add(match[1]);
            }

            if (placeholders.size > 0) {
                noPlaceholderMessage.style.display = 'none';
                placeholders.forEach(placeholder => {
                    const div = document.createElement('div');
                    const label = document.createElement('label');
                    label.htmlFor = `placeholder_${placeholder}`;
                    label.className = 'block font-semibold text-sm text-gray-700 mb-1';
                    label.textContent = `Isi untuk: ${placeholder.replace(/_/g, ' ')}`;

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = `placeholders[${placeholder}]`;
                    input.id = `placeholder_${placeholder}`;
                    input.className = 'mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
                    input.setAttribute('data-placeholder', placeholder);

                    div.appendChild(label);
                    div.appendChild(input);
                    placeholderContainer.appendChild(div);
                });
            }

            // Optional: Update final content on form submit to ensure it's the latest
            document.querySelector('form').addEventListener('submit', function() {
                let finalContent = templateContent;
                document.querySelectorAll('#placeholder-inputs input').forEach(input => {
                    const placeholder = input.getAttribute('data-placeholder');
                    const value = input.value;
                    finalContent = finalContent.replace(new RegExp(`\\{\\{${placeholder}\\}\\}`, 'g'), value);
                });
                finalContentTextarea.value = finalContent;
            });
        });
    </script>
    @endpush
</x-app-layout>
