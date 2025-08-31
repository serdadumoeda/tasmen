<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Surat Keluar Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900 text-center">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Pilih Metode Pembuatan Surat</h3>
                    <p class="text-gray-600 mb-8">Anda dapat membuat surat baru menggunakan template yang sudah ada, atau mengunggah dokumen yang sudah jadi.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Opsi 1: Buat dari Template --}}
                        <a href="{{ route('surat-keluar.create.from-template', ['suratable_id' => $suratable_id ?? null, 'suratable_type' => $suratable_type ?? null]) }}" class="block p-8 bg-indigo-50 border-2 border-transparent rounded-lg shadow-lg hover:shadow-2xl hover:border-indigo-500 transition-all duration-300 transform hover:-translate-y-2">
                            <div class="text-center">
                                <i class="fas fa-file-alt fa-4x text-indigo-500 mb-4"></i>
                                <h4 class="font-bold text-xl text-indigo-800">Buat dari Template</h4>
                                <p class="text-sm text-gray-600 mt-2">Gunakan editor di dalam aplikasi untuk membuat surat dari template yang telah disetujui.</p>
                            </div>
                        </a>

                        {{-- Opsi 2: Upload Dokumen Jadi --}}
                        <a href="{{ route('surat-keluar.create.upload', ['suratable_id' => $suratable_id ?? null, 'suratable_type' => $suratable_type ?? null]) }}" class="block p-8 bg-green-50 border-2 border-transparent rounded-lg shadow-lg hover:shadow-2xl hover:border-green-500 transition-all duration-300 transform hover:-translate-y-2">
                            <div class="text-center">
                                <i class="fas fa-file-upload fa-4x text-green-500 mb-4"></i>
                                <h4 class="font-bold text-xl text-green-800">Upload Dokumen Jadi</h4>
                                <p class="text-sm text-gray-600 mt-2">Unggah file PDF surat yang sudah final untuk diarsipkan dan didisposisikan melalui sistem.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
