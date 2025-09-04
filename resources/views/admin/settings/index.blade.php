<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Umum') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan Umum Aplikasi</h3>

                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 border border-green-300 rounded-md p-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            {{-- Kolom Kop Surat --}}
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Kop Surat</h4>
                                <div>
                                    <label for="letterhead_line_1" class="block text-sm font-medium text-gray-700">Baris 1</label>
                                    <input type="text" id="letterhead_line_1" name="letterhead_line_1" value="{{ old('letterhead_line_1', $settings['letterhead_line_1'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="letterhead_line_2" class="block text-sm font-medium text-gray-700">Baris 2</label>
                                    <input type="text" id="letterhead_line_2" name="letterhead_line_2" value="{{ old('letterhead_line_2', $settings['letterhead_line_2'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="letterhead_line_3" class="block text-sm font-medium text-gray-700">Baris 3</label>
                                    <input type="text" id="letterhead_line_3" name="letterhead_line_3" value="{{ old('letterhead_line_3', $settings['letterhead_line_3'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="letterhead_line_4" class="block text-sm font-medium text-gray-700">Baris 4</label>
                                    <input type="text" id="letterhead_line_4" name="letterhead_line_4" value="{{ old('letterhead_line_4', $settings['letterhead_line_4'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                 <div>
                                    <label for="letterhead_line_5" class="block text-sm font-medium text-gray-700">Baris 5</label>
                                    <input type="text" id="letterhead_line_5" name="letterhead_line_5" value="{{ old('letterhead_line_5', $settings['letterhead_line_5'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label for="logo_path" class="block text-sm font-medium text-gray-700">Logo</label>
                                    <input type="file" id="logo_path" name="logo_path" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    @if(isset($settings['logo_path']) && $settings['logo_path'])
                                        <div class="mt-4">
                                            <p class="text-sm text-gray-600 mb-2">Logo Saat Ini:</p>
                                            <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Current Logo" class="max-h-24 rounded-md border bg-gray-50 p-2">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Kolom Blok Penandatangan --}}
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Blok Penandatangan</h4>
                                 <div>
                                    <label for="signer_block_line_1" class="block text-sm font-medium text-gray-700">Baris 1</label>
                                    <input type="text" id="signer_block_line_1" name="signer_block_line_1" value="{{ old('signer_block_line_1', $settings['signer_block_line_1'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                 <div>
                                    <label for="signer_block_line_2" class="block text-sm font-medium text-gray-700">Baris 2</label>
                                    <input type="text" id="signer_block_line_2" name="signer_block_line_2" value="{{ old('signer_block_line_2', $settings['signer_block_line_2'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                 <div>
                                    <label for="signer_block_line_3" class="block text-sm font-medium text-gray-700">Baris 3 (Setelah Nama)</label>
                                    <input type="text" id="signer_block_line_3" name="signer_block_line_3" value="{{ old('signer_block_line_3', $settings['signer_block_line_3'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="signer_block_line_4" class="block text-sm font-medium text-gray-700">Baris 4</label>
                                    <input type="text" id="signer_block_line_4" name="signer_block_line_4" value="{{ old('signer_block_line_4', $settings['signer_block_line_4'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            {{-- Kolom Pengaturan Beban Kerja --}}
                            <div class="space-y-4 md:col-span-2 border-t pt-8 mt-8">
                                <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Pengaturan Beban Kerja</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="workload_standard_hours" class="block text-sm font-medium text-gray-700">Standar Jam Kerja Mingguan</label>
                                        <input type="text" id="workload_standard_hours" name="workload_standard_hours" value="{{ old('workload_standard_hours', $settings['workload_standard_hours'] ?? '37.5') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <p class="text-xs text-gray-500 mt-1">Gunakan titik sebagai pemisah desimal.</p>
                                    </div>
                                    <div>
                                        <label for="workload_threshold_normal" class="block text-sm font-medium text-gray-700">Batas Atas Zona Hijau (Normal)</label>
                                        <input type="text" id="workload_threshold_normal" name="workload_threshold_normal" value="{{ old('workload_threshold_normal', $settings['workload_threshold_normal'] ?? '0.75') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <p class="text-xs text-gray-500 mt-1">Contoh: 0.75 berarti di bawah 75% dianggap normal.</p>
                                    </div>
                                    <div>
                                        <label for="workload_threshold_warning" class="block text-sm font-medium text-gray-700">Batas Atas Zona Kuning (Peringatan)</label>
                                        <input type="text" id="workload_threshold_warning" name="workload_threshold_warning" value="{{ old('workload_threshold_warning', $settings['workload_threshold_warning'] ?? '1.0') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <p class="text-xs text-gray-500 mt-1">Contoh: 1.0 berarti antara 75% dan 100% dianggap peringatan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-5 border-t border-gray-200">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
