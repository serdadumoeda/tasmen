<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Surat Keluar - Langkah 1: Pilih Template') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Pilih Template Surat</h3>
                    <p class="text-sm text-gray-600 mb-6">Pilih salah satu template di bawah ini sebagai dasar untuk surat baru Anda.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($templates as $template)
                            <a href="{{ route('surat-keluar.create.from-template', ['template_id' => $template->id]) }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-xl hover:border-indigo-500 transition-all duration-300 transform hover:-translate-y-1">
                                <h4 class="font-bold text-lg text-indigo-700">{{ $template->judul }}</h4>
                                <p class="text-sm text-gray-600 mt-2">{{ $template->deskripsi }}</p>
                            </a>
                        @empty
                            <div class="lg:col-span-3 text-center text-gray-500 p-10 bg-gray-50 rounded-lg shadow-inner">
                                <i class="fas fa-file-excel fa-3x text-gray-400 mb-4"></i>
                                <p class="text-lg">Tidak ada template yang tersedia.</p>
                                <p class="text-sm text-gray-400 mt-2">Silakan buat template terlebih dahulu di menu Manajemen Template Surat.</p>
                                <a href="{{ route('templatesurat.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold text-xs uppercase">
                                    Buat Template
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
