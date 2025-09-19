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
@endphp

<div class="grid grid-cols-1 @if(!$isCompleteProfile) md:grid-cols-3 @endif gap-8">
    @if(!$isCompleteProfile)
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Pribadi</h3>
        {{ form_input('Nama Lengkap', 'name', $user, 'text', true, 'autofocus') }}
        {{ form_input('Email', 'email', $user, 'email', true) }}
        {{ form_input('NIP', 'nip', $user, 'text', true) }}
    </div>
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Kepegawaian</h3>
        {{ form_input('Golongan', 'golongan', $user) }}
        {{ form_input('Eselon', 'eselon', $user) }}
    </div>
    @endif

    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Unit Kerja & Jabatan</h3>
        <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $user->unit_id) }}">

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
            <label for="jabatan_name" class="block font-semibold text-sm text-gray-700 mb-1">5. Nama Jabatan <span class="text-red-500 font-bold">*</span></label>
            <input type="text" name="jabatan_name" id="jabatan_name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300" placeholder="Contoh: Analis Hukum Ahli Madya" value="{{ old('jabatan_name', optional($user->jabatan)->name) }}" required>
        </div>

        @if(!$isCompleteProfile)
            <div class="mb-4">
                <label for="atasan_id" class="block font-semibold text-sm text-gray-700 mb-1">Atasan Langsung</label>
                <select name="atasan_id" id="atasan_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300">
                     <option value="">-- Tidak ada --</option>
                     @foreach($supervisors ?? [] as $supervisor)
                        <option value="{{ $supervisor->id }}" @selected(old('atasan_id', $user->atasan_id ?? '') == $supervisor->id)>{{ $supervisor->name }}</option>
                    @endforeach
                </select>
            </div>
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
    const isCompleteProfile = @json($isCompleteProfile);

    const unitSelects = document.querySelectorAll('.unit-select');
    const unitIdInput = document.getElementById('unit_id');
    const submitButton = document.getElementById('submit_button');
    const jabatanNameInput = document.getElementById('jabatan_name');

    const checkFormValidity = () => {
        if (!submitButton) return;
        const unitIsSelected = unitIdInput.value !== '';
        const jabatanIsFilled = jabatanNameInput.value.trim() !== '';
        submitButton.disabled = !(unitIsSelected && jabatanIsFilled);
    };

    const fetchJson = async (url) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (e) {
            console.error("Fetch error:", e);
            return [];
        }
    };

    const populateSelect = (selectEl, data, placeholder) => {
        selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        if (data.length > 0) {
            data.forEach(item => selectEl.add(new Option(item.name, item.id)));
            selectEl.disabled = false;
        } else {
            selectEl.innerHTML = `<option value="">-- Tidak ada pilihan --</option>`;
            selectEl.disabled = true;
        }
    };

    const handleUnitSelectChange = async (event) => {
        const selectEl = event.target;
        const selectedUnitId = selectEl.value;
        const level = parseInt(selectEl.dataset.level, 10);
        let finalUnitId = '';

        for (let i = level; i < unitSelects.length; i++) {
            const currentSelect = unitSelects[i];
            currentSelect.innerHTML = `<option value="">${currentSelect.dataset.placeholder}</option>`;
            currentSelect.disabled = true;
        }

        if (selectedUnitId) {
            finalUnitId = selectedUnitId;
        } else if (level > 1) {
            finalUnitId = unitSelects[level - 2].value;
        }

        unitIdInput.value = finalUnitId;
        checkFormValidity();

        if (selectedUnitId) {
            const nextLevel = level + 1;
            const nextSelect = document.querySelector(`.unit-select[data-level='${nextLevel}']`);
            if (nextSelect) {
                nextSelect.disabled = true;
                nextSelect.innerHTML = `<option value="">-- Memuat... --</option>`;
                const children = await fetchJson(`/api/units/${selectedUnitId}/children`);
                populateSelect(nextSelect, children, nextSelect.dataset.placeholder);
            }
        }
    };

    unitSelects.forEach(selectEl => {
        selectEl.addEventListener('change', handleUnitSelectChange);
    });

    if (jabatanNameInput) {
        jabatanNameInput.addEventListener('input', checkFormValidity);
    }
});
</script>
@endpush
