<section>
    <header>
        <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center"> {{-- Menyesuaikan ukuran dan ketebalan teks --}}
            <i class="fas fa-user-circle mr-2 text-indigo-600"></i> {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 mb-6"> {{-- Menyesuaikan margin bawah --}}
            {{ __("Perbarui informasi profil dan alamat email akun Anda.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6"> {{-- Menyesuaikan spasi --}}
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block font-semibold text-sm text-gray-700 mb-1"> {{-- Styling label konsisten --}}
                <i class="fas fa-user mr-2 text-gray-500"></i> {{ __('Nama') }} <span class="text-red-500">*</span>
            </label>
            <input id="name" name="name" type="text" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block font-semibold text-sm text-gray-700 mb-1"> {{-- Styling label konsisten --}}
                <i class="fas fa-envelope mr-2 text-gray-500"></i> {{ __('Email') }} <span class="text-red-500">*</span>
            </label>
            <input id="email" name="email" type="email" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-r-lg shadow-sm"> {{-- Styling info verifikasi lebih menonjol --}}
                    <p class="text-sm flex items-center">
                        <i class="fas fa-info-circle mr-2 text-yellow-500"></i> {{ __('Alamat email Anda belum diverifikasi.') }}
                    </p>

                    <button form="send-verification" class="underline text-sm text-yellow-700 hover:text-yellow-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 mt-2 inline-block">
                        {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                    </button>
                </div>
            @endif
        </div>

        <div>
            <label for="signature_image" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-signature mr-2 text-gray-500"></i> {{ __('Gambar Tanda Tangan') }}
            </label>
            @if ($user->signature_image_path)
                <div class="mt-2 mb-2">
                    <img src="{{ Storage::url($user->signature_image_path) }}" alt="Tanda Tangan" class="h-20 w-auto border p-1 rounded-md shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Tanda tangan saat ini. Unggah file baru untuk mengganti.</p>
                </div>
            @endif
            <input id="signature_image" name="signature_image" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/png"/>
            <p class="text-xs text-gray-500 mt-1">Hanya file PNG yang diizinkan. Maksimal 1MB.</p>
            @error('signature_image') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-4 mt-8 pt-6 border-t border-gray-200"> {{-- Menyesuaikan margin, padding, border --}}
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105"> {{-- Styling tombol Save konsisten --}}
                <i class="fas fa-save mr-2"></i> {{ __('Simpan') }}
            </button>

            @if (session('status') === 'profile-updated')
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