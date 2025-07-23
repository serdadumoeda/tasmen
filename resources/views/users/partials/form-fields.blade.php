@if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} error pada input Anda.</h3>
                 <div class="mt-2 text-sm text-red-700">
                    <ul role="list" class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Kolom Kiri --}}
    <div>
        <div>
            <x-input-label for="name" :value="__('Nama Jabatan')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name ?? '')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email ?? '')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="role" :value="__('Role / Jabatan')" />
            <select name="role" id="role" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @php
                    $roles = [
                        App\Models\User::ROLE_ESELON_I,
                        App\Models\User::ROLE_ESELON_II,
                        App\Models\User::ROLE_KOORDINATOR,
                        App\Models\User::ROLE_SUB_KOORDINATOR,
                        App\Models\User::ROLE_STAF
                    ];
                @endphp
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') == $role)>{{ $role }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="unit_eselon_1" :value="__('Unit Eselon I (Opsional)')" />
            <select name="unit_eselon_1" id="unit_eselon_1" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Unit Eselon I --</option>
                @foreach($eselon1Units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_eselon_1', $user->unit->parentUnit->id ?? '') == $unit->id)>{{ $unit->name }}</option>
                @endforeach
            </select>
             <x-input-error :messages="$errors->get('unit_eselon_1')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="unit_id" :value="__('Unit Eselon II (Opsional)')" />
            <select name="unit_id" id="unit_eselon_2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Unit Eselon II --</option>
            </select>
            <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
        </div>
    </div>

    {{-- Kolom Kanan --}}
    <div>
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" :required="!isset($user)" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            @if(isset($user))
                <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
            @endif
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="status" :value="__('Status')" />
            <select name="status" id="status" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="active" @selected(old('status', $user->status ?? 'active') == 'active')>Aktif</option>
                <option value="suspended" @selected(old('status', $user->status ?? '') == 'suspended')>Ditangguhkan</option>
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eselon1Select = document.getElementById('unit_eselon_1');
    const eselon2Select = document.getElementById('unit_eselon_2');

    function fetchEselon2Units(eselon1Id, selectedEselon2Id = null) {
        if (!eselon1Id) {
            eselon2Select.innerHTML = '<option value="">-- Pilih Unit Eselon II --</option>';
            return;
        }

        // Tampilkan loading
        eselon2Select.innerHTML = '<option value="">Memuat...</option>';

        fetch(`/api/units/${eselon1Id}/children`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                let options = '<option value="">-- Pilih Unit Eselon II --</option>';
                data.forEach(unit => {
                    const isSelected = unit.id == selectedEselon2Id ? 'selected' : '';
                    options += `<option value="${unit.id}" ${isSelected}>${unit.name}</option>`;
                });
                eselon2Select.innerHTML = options;
            })
            .catch(error => {
                console.error('Error fetching Eselon II units:', error);
                eselon2Select.innerHTML = '<option value="">Gagal memuat data</option>';
            });
    }

    eselon1Select.addEventListener('change', function () {
        fetchEselon2Units(this.value);
    });

    // --- Logic for Edit Page ---
    // Jika ada nilai lama (karena validation error) atau nilai dari model ($user)
    const eselon1OldValue = '{{ old('unit_eselon_1', $user->unit->parentUnit->id ?? '') }}';
    const eselon2OldValue = '{{ old('unit_id', $user->unit_id ?? '') }}';

    if (eselon1OldValue) {
        eselon1Select.value = eselon1OldValue;
        fetchEselon2Units(eselon1OldValue, eselon2OldValue);
    }
});
</script>
@endpush
