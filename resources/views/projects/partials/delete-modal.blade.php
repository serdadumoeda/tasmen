@props(['project'])

{{-- Modal Konfirmasi Hapus --}}
<div x-show="showDeleteModal"
     class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
     x-cloak
     @keydown.escape.window="showDeleteModal = false">

    <div @click.away="showDeleteModal = false"
         class="relative mx-auto p-8 border w-full max-w-md shadow-2xl rounded-xl bg-white"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="text-center">
            {{-- Ikon Peringatan --}}
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-5">Hapus Kegiatan</h3>

            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Apakah Anda yakin ingin menghapus kegiatan <strong>"{{ $project->name }}"</strong>? Tindakan ini tidak dapat diurungkan. Semua tugas dan data terkait akan dihapus secara permanen.
                </p>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-center mt-4 space-x-4">
                <x-secondary-button @click="showDeleteModal = false">
                    Batal
                </x-secondary-button>

                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <x-danger-button type="submit">
                        Ya, Hapus
                    </x-danger-button>
                </form>
            </div>
        </div>
    </div>
</div>
