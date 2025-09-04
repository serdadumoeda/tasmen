<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    <div class="py-12 bg-gray-50"> 
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Bagian Update Profile Information --}}
            <div class="p-4 sm:p-8 bg-white shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="max-w-xl">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-indigo-600"></i> {{ __('Informasi Profil') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 mb-6">
                        {{ __("Perbarui informasi profil dan alamat email akun Anda.") }}
                    </p>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Bagian Update Password --}}
            <div class="p-4 sm:p-8 bg-white shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="max-w-xl">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-lock mr-2 text-green-600"></i> {{ __('Perbarui Kata Sandi') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 mb-6">
                        {{ __("Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.") }}
                    </p>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Bagian Delete User --}}
            <div class="p-4 sm:p-8 bg-white shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="max-w-xl">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-slash mr-2 text-red-600"></i> {{ __('Hapus Akun') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 mb-6">
                        {{ __("Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.") }}
                    </p>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>