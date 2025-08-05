<section>
    <header>
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center"> {{-- Menyesuaikan ukuran dan ketebalan teks --}}
            <i class="fas fa-user-slash mr-2 text-red-600"></i> {{ __('Hapus Akun') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 mb-6"> {{-- Menyesuaikan margin bawah --}}
            {{ __('Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.') }}
        </p>
    </header>

    {{-- Tombol Pemicu Modal Hapus Akun --}}
    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"
    >
        <i class="fas fa-trash-alt mr-2"></i> {{ __('Hapus Akun') }}
    </button>

    {{-- Modal Konfirmasi Hapus Akun --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-white rounded-lg shadow-xl"> {{-- Padding, background, rounded, shadow untuk form modal --}}
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center"> {{-- Ukuran dan ketebalan teks, icon --}}
                <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i> {{ __('Apakah Anda yakin ingin menghapus akun Anda?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 mb-6"> {{-- Menyesuaikan margin --}}
                {{ __('Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Harap masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">{{ __('Password') }}</label> {{-- Label tersembunyi untuk aksesibilitas --}}

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" {{-- Styling input konsisten --}}
                    placeholder="{{ __('Password') }}"
                />

                @error('password', 'userDeletion') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3"> {{-- Spasi antar tombol --}}
                {{-- Tombol Batal --}}
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"
                >
                    <i class="fas fa-times mr-2"></i> {{ __('Batal') }}
                </button>

                {{-- Tombol Hapus Akun --}}
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"
                >
                    <i class="fas fa-trash-alt mr-2"></i> {{ __('Hapus Akun') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>