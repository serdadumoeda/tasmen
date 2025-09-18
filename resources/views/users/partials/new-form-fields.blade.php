@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
        <strong class="font-bold">Oops! Ada yang salah:</strong>
        <ul class="mt-1.5 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Helper function for form fields --}}
@php
$isCompleteProfile = ($context ?? '') === 'complete_profile';

function form_input($label, $name, $user, $type = 'text', $is_required = false, $extra_attrs = '') {
    $value = old($name, $user->{$name} ?? '');
    $required_attr = $is_required ? 'required' : '';
    $required_span = $is_required ? '<span class="text-red-500 font-bold">*</span>' : '';
    echo "<div class='mb-4'>";
    echo "<label for='{$name}' class='block font-semibold text-sm text-gray-700 mb-1'>{$label} {$required_span}</label>";
    echo "<input id='{$name}' class='block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' type='{$type}' name='{$name}' value='{$value}' {$required_attr} {$extra_attrs} />";
    echo "</div>";
}
function form_textarea($label, $name, $user, $is_required = false) {
    $value = old($name, $user->{$name} ?? '');
    $required_attr = $is_required ? 'required' : '';
    $required_span = $is_required ? '<span class="text-red-500 font-bold">*</span>' : '';
    echo "<div class='mb-4'>";
    echo "<label for='{$name}' class='block font-semibold text-sm text-gray-700 mb-1'>{$label} {$required_span}</label>";
    echo "<textarea id='{$name}' class='block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' name='{$name}' {$required_attr}>{$value}</textarea>";
    echo "</div>";
}
@endphp

<div class="grid grid-cols-1 @if(!$isCompleteProfile) md:grid-cols-3 @endif gap-8">
    @if(!$isCompleteProfile)
    {{-- Kolom Kiri: Informasi Pribadi --}}
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Pribadi</h3>
        {{ form_input('Nama Lengkap', 'name', $user, 'text', true, 'autofocus') }}
        {{ form_input('Email', 'email', $user, 'email', true) }}
        {{ form_input('NIK', 'nik', $user, 'text', false, 'placeholder="16 digit NIK"') }}
        {{ form_input('NIP', 'nip', $user, 'text', true) }}
        {{ form_input('Tempat Lahir', 'tempat_lahir', $user) }}
        {{ form_input('Tgl. Lahir', 'tgl_lahir', $user, 'text', true, 'placeholder="YYYY-MM-DD"') }}
        {{ form_textarea('Alamat', 'alamat', $user) }}
        <div class="mb-4">
            <label for="jenis_kelamin" class="block font-semibold text-sm text-gray-700 mb-1">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Pilih --</option>
                <option value="L" @selected(old('jenis_kelamin', $user->jenis_kelamin ?? '') == 'L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin', $user->jenis_kelamin ?? '') == 'P')>Perempuan</option>
            </select>
        </div>
        {{ form_input('Agama', 'agama', $user) }}
        {{ form_input('No. HP', 'no_hp', $user) }}
        {{ form_input('Telepon', 'telepon', $user) }}
        {{ form_input('NPWP', 'npwp', $user) }}
    </div>

    {{-- Kolom Tengah: Informasi Kepegawaian --}}
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Kepegawaian</h3>
        {{ form_input('Golongan', 'golongan', $user) }}
        {{ form_input('Eselon', 'eselon', $user) }}
        {{ form_input('TMT Eselon', 'tmt_eselon', $user, 'text', false, 'placeholder="YYYY-MM-DD"') }}
        {{ form_input('Jenis Jabatan', 'jenis_jabatan', $user) }}
        {{ form_input('Grade', 'grade', $user) }}
        {{ form_input('Pendidikan Terakhir', 'pendidikan_terakhir', $user) }}
        {{ form_input('Jurusan', 'pendidikan_jurusan', $user) }}
        {{ form_input('Universitas', 'pendidikan_universitas', $user) }}
        {{ form_input('TMT CPNS', 'tmt_cpns', $user, 'text', false, 'placeholder="YYYY-MM-DD"') }}
        {{ form_input('TMT PNS', 'tmt_pns', $user, 'text', false, 'placeholder="YYYY-MM-DD"') }}
    </div>
    @endif

    {{-- Kolom Kanan: Unit & Akses --}}
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Unit Kerja & Akses</h3>
        <div class="mb-4">
            <label for="eselon_i" class="block font-semibold text-sm text-gray-700 mb-1">1. Unit Eselon I</label>
            <select id="eselon_i" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="1" data-placeholder="-- Pilih Unit Eselon I --">
                <option value="">-- Pilih Unit Eselon I --</option>
                @foreach($eselonIUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="eselon_ii" class="block font-semibold text-sm text-gray-700 mb-1">2. Unit Eselon II</label>
            <select id="eselon_ii" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="2" data-placeholder="-- Pilih Unit Eselon II --" disabled><option value="">-- Pilih Unit Eselon I Dahulu --</option></select>
        </div>
        <div class="mb-4">
            <label for="koordinator" class="block font-semibold text-sm text-gray-700 mb-1">3. Koordinator</label>
            <select id="koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="3" data-placeholder="-- Pilih Koordinator --" disabled><option value="">-- Pilih Unit Eselon II Dahulu --</option></select>
        </div>
        <div class="mb-4">
            <label for="sub_koordinator" class="block font-semibold text-sm text-gray-700 mb-1">4. Sub Koordinator</label>
            <select id="sub_koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="4" data-placeholder="-- Pilih Sub Koordinator --" disabled><option value="">-- Pilih Koordinator Dahulu --</option></select>
        </div>
        <div class="mb-4">
            <label for="jabatan_id" class="block font-semibold text-sm text-gray-700 mb-1">5. Jabatan (Opsional)</label>
            <select name="jabatan_id" id="jabatan_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300" disabled><option value="">-- Pilih Unit Kerja Terakhir --</option></select>
            <p class="text-xs text-gray-500 mt-1">Jika dikosongkan, pengguna harus melengkapi profil saat login pertama.</p>
        </div>

        <div class="mb-4">
            <label for="atasan_id" class="block font-semibold text-sm text-gray-700 mb-1">Atasan Langsung</label>
            <select name="atasan_id" id="atasan_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300">
                 <option value="">-- Tidak ada --</option>
                 @foreach($supervisors as $supervisor)
                    <option value="{{ $supervisor->id }}" @selected(old('atasan_id', $user->atasan_id ?? '') == $supervisor->id)>{{ $supervisor->name }}</option>
                @endforeach
            </select>
        </div>

        @can('manageUsers', App\Models\User::class)
        <div class="border-t my-6"></div>
        <div class="mb-4">
            <label for="is_kepala_unit" class="flex items-center">
                <input type="checkbox" name="is_kepala_unit" id="is_kepala_unit" value="1" @checked(old('is_kepala_unit', $user->id && $user->unit && $user->id === $user->unit->kepala_unit_id)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-600 font-semibold">Jadikan Kepala Unit</span>
            </label>
            <p class="mt-1 text-xs text-gray-500 ml-6">Menetapkan pengguna ini sebagai kepala dari unit kerja mereka saat ini.</p>
        </div>

        @if ($user->exists)
        <div class="mb-4">
            <a href="{{ route('admin.users.leave-balance.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                <i class="fas fa-calculator mr-2"></i> Atur Saldo Cuti
            </a>
            <p class="mt-1 text-xs text-gray-500">Mengatur sisa cuti tahunan dari tahun sebelumnya secara manual.</p>
        </div>
        @endif
        @endcan

        @if(!$isCompleteProfile)
        <div class="mb-4">
            <label for="password" class="block font-semibold text-sm text-gray-700 mb-1">Password</label>
            <input id="password" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300" type="password" name="password" autocomplete="new-password">
            @if($user->exists)<p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah.</p>@endif
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="block font-semibold text-sm text-gray-700 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300" type="password" name="password_confirmation">
        </div>
        <div class="mb-4">
            <label for="status" class="block font-semibold text-sm text-gray-700 mb-1">Status</label>
            <select name="status" id="status" required class="block mt-1 w-full rounded-lg shadow-sm border-gray-300">
                <option value="active" @selected(old('status', $user->status ?? 'active') == 'active')>Aktif</option>
                <option value="suspended" @selected(old('status', $user->status ?? '') == 'suspended')>Ditangguhkan</option>
            </select>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const unitSelects = document.querySelectorAll('.unit-select');
    const jabatanSelect = document.getElementById('jabatan_id');
    const unitIdInput = document.getElementById('unit_id'); // Assuming you have a hidden input with this id
    const selectedUnitPath = @json($selectedUnitPath ?? []);
    const oldJabatanId = '{{ old('jabatan_id', optional($user->jabatan)->id ?? '') }}';

    const fetchJson = async (url) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (e) {
            console.error("Fetch error:", e);
            return []; // Return empty array on error
        }
    };

    const populateSelect = (selectEl, data, placeholder) => {
        selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        if (data.length > 0) {
            data.forEach(item => {
                const option = new Option(item.name, item.id);
                selectEl.add(option);
            });
            selectEl.disabled = false;
        } else {
            selectEl.innerHTML = `<option value="">-- Tidak ada pilihan --</option>`;
            selectEl.disabled = true;
        }
    };

    const fetchAndPopulateJabatans = async (unitId, selectedId = null) => {
        jabatanSelect.disabled = true;
        jabatanSelect.innerHTML = `<option value="">-- Memuat Jabatan... --</option>`;
        if (!unitId) {
            jabatanSelect.innerHTML = `<option value="">-- Pilih Unit Kerja Terakhir --</option>`;
            return;
        }

        let url = `/api/units/${unitId}/vacant-jabatans`;
        @if ($user->exists)
            if ('{{ $isCompleteProfile }}' !== '1') {
                url += `?user_id={{ $user->id }}`;
            }
        @endif

        const data = await fetchJson(url);
        populateSelect(jabatanSelect, data, '-- Pilih Jabatan --');
        if (selectedId) {
            jabatanSelect.value = selectedId;
        }
    };

    const handleUnitSelectChange = async (event) => {
        const selectEl = event.target;
        const unitId = selectEl.value;
        const level = parseInt(selectEl.dataset.level, 10);

        // Reset all subsequent dropdowns
        for (let i = level; i < unitSelects.length; i++) {
            const currentSelect = unitSelects[i];
            currentSelect.innerHTML = `<option value="">${currentSelect.dataset.placeholder}</option>`;
            currentSelect.disabled = true;
        }
        jabatanSelect.innerHTML = `<option value="">-- Pilih Unit Kerja Terakhir --</option>`;
        jabatanSelect.disabled = true;

        if (!unitId) {
            // If a select is cleared, fetch jabatans for the parent unit
            if (level > 1) {
                const parentUnitId = unitSelects[level - 2].value;
                if(parentUnitId) await fetchAndPopulateJabatans(parentUnitId);
            }
            return;
        }

        // Fetch jabatans for the currently selected unit
        await fetchAndPopulateJabatans(unitId, oldJabatanId);

        // Fetch children for the next level dropdown
        const nextLevel = level + 1;
        const nextSelect = document.querySelector(`.unit-select[data-level='${nextLevel}']`);
        if (nextSelect) {
            nextSelect.disabled = true;
            nextSelect.innerHTML = `<option value="">-- Memuat... --</option>`;
            const children = await fetchJson(`/api/units/${unitId}/children`);
            populateSelect(nextSelect, children, nextSelect.dataset.placeholder);
        }
    };

    const initializeForm = async () => {
        for (let i = 0; i < unitSelects.length; i++) {
            const selectEl = unitSelects[i];
            selectEl.addEventListener('change', handleUnitSelectChange);

            if (selectedUnitPath.length > i) {
                const unitIdToSelect = selectedUnitPath[i];

                // For the first dropdown, we can set value and trigger change directly
                if (i === 0) {
                    selectEl.value = unitIdToSelect;
                    await handleUnitSelectChange({ target: selectEl });
                } else {
                    // For subsequent dropdowns, wait for them to be populated
                    // A simple way is to trust the chain, and set the value
                    // The 'await' on handleUnitSelectChange should make this work sequentially
                    const previousSelect = unitSelects[i-1];
                    if (previousSelect.value == selectedUnitPath[i-1]) {
                         selectEl.value = unitIdToSelect;
                         if (selectEl.value == unitIdToSelect) { // Check if value was set
                             await handleUnitSelectChange({ target: selectEl });
                         }
                    }
                }
            }
        }
    };

    initializeForm();
});
</script>
@endpush
