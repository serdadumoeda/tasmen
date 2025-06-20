<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" x-data="registrationForm()">
        @csrf

        <h2 class="text-xl font-bold text-center mb-4">Registrasi Pengguna Baru</h2>

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="eselon_2_id" :value="__('Unit Eselon II')" />
            <select name="eselon_2_id" id="eselon_2_id" x-model="selectedUnit" @change="fetchSuperiors" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Unit Kerja --</option>
                @foreach($eselon2Users as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('eselon_2_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="role" :value="__('Role / Jabatan')" />
            <select name="role" id="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                 @php
                    $roles = ['Koordinator', 'Ketua Tim', 'Sub Koordinator', 'Staff'];
                @endphp
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="parent_id" :value="__('Atasan Langsung')" />
            <select name="parent_id" id="parent_id" x-ref="superiors" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Unit Kerja terlebih dahulu --</option>
            </select>
            <p x-show="loading" class="text-xs text-gray-500 mt-1">Memuat daftar atasan...</p>
            <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
        </div>


        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Sudah terdaftar?') }}
            </a>
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
    
    <script>
        function registrationForm() {
            return {
                selectedUnit: '',
                loading: false,
                fetchSuperiors() {
                    if (!this.selectedUnit) {
                        this.$refs.superiors.innerHTML = '<option value="">-- Pilih Unit Kerja terlebih dahulu --</option>';
                        return;
                    }
                    
                    this.loading = true;
                    this.$refs.superiors.innerHTML = '<option value="">Memuat...</option>';

                    // PERBAIKAN: URL fetch diubah dari /api/... menjadi /get-users-by-unit/...
                    fetch(`/get-users-by-unit/${this.selectedUnit}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            let options = '<option value="">-- Pilih Atasan Langsung --</option>';
                            data.forEach(user => {
                                options += `<option value="${user.id}">${user.name} (${user.role})</option>`;
                            });
                            this.$refs.superiors.innerHTML = options;
                            this.loading = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.$refs.superiors.innerHTML = '<option value="">Gagal memuat data. Periksa koneksi atau konsol.</option>';
                            this.loading = false;
                        });
                }
            }
        }
    </script>
</x-guest-layout>