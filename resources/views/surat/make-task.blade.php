<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jadikan Surat sebagai Tugas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Konfirmasi Pembuatan Tugas
                    </h3>

                    <p class="mb-4 text-gray-600">
                        Anda akan membuat tugas baru berdasarkan surat dengan perihal: <strong>"{{ $surat->perihal }}"</strong>.
                    </p>
                    <p class="mb-6 text-gray-600">
                        Tugas akan dibuat dengan status "Pending" dan tenggat waktu default 7 hari dari sekarang. Anda dapat mengubah detail ini setelah tugas dibuat.
                    </p>

                    <div class="flex items-center justify-start space-x-4">
                        <form action="{{ route('surat.make-task', $surat->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-check mr-2"></i> Ya, Buat Tugas
                            </button>
                        </form>
                        <a href="{{ route('surat.show', $surat->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
