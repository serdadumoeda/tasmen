<section>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6"> {{-- Menyesuaikan spasi --}}
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block font-semibold text-sm text-gray-700 mb-1"> {{-- Styling label konsisten --}}
                <i class="fas fa-key mr-2 text-gray-500"></i> {{ __('Kata Sandi Saat Ini') }} <span class="text-red-500">*</span>
            </label>
            <input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" autocomplete="current-password" />
            @error('current_password', 'updatePassword') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="update_password_password" class="block font-semibold text-sm text-gray-700 mb-1"> {{-- Styling label konsisten --}}
                <i class="fas fa-lock-open mr-2 text-gray-500"></i> {{ __('Kata Sandi Baru') }} <span class="text-red-500">*</span>
            </label>
            <input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" autocomplete="new-password" />
            @error('password', 'updatePassword') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block font-semibold text-sm text-gray-700 mb-1"> {{-- Styling label konsisten --}}
                <i class="fas fa-check-double mr-2 text-gray-500"></i> {{ __('Konfirmasi Kata Sandi') }} <span class="text-red-500">*</span>
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-4 mt-8 pt-6 border-t border-gray-200"> {{-- Menyesuaikan margin, padding, border --}}
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Styling tombol Save konsisten --}}
                <i class="fas fa-save mr-2"></i> {{ __('Simpan') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 flex items-center"
                ><i class="fas fa-check-circle mr-2"></i> {{ __('Disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
