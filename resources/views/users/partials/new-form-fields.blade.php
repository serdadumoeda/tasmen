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
function form_input($label, $name, $user, $type = 'text', $is_required = false, $extra_attrs = '') {
    $value = old($name, $user->{$name} ?? '');
    $required_attr = $is_required ? 'required' : '';
    echo "<div class='mb-4'>";
    echo "<label for='{$name}' class='block font-semibold text-sm text-gray-700 mb-1'>{$label}</label>";
    echo "<input id='{$name}' class='block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' type='{$type}' name='{$name}' value='{$value}' {$required_attr} {$extra_attrs} />";
    echo "</div>";
}
function form_textarea($label, $name, $user, $is_required = false) {
    $value = old($name, $user->{$name} ?? '');
    $required_attr = $is_required ? 'required' : '';
    echo "<div class='mb-4'>";
    echo "<label for='{$name}' class='block font-semibold text-sm text-gray-700 mb-1'>{$label}</label>";
    echo "<textarea id='{$name}' class='block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' name='{$name}' {$required_attr}>{$value}</textarea>";
    echo "</div>";
}
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    {{-- Kolom Kiri: Informasi Pribadi --}}
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Pribadi</h3>
        {{ form_input('Nama Lengkap', 'name', $user, 'text', true) }}
        {{ form_input('Email', 'email', $user, 'email', true) }}
        {{ form_input('NIP', 'nip', $user) }}
        {{ form_input('Tempat Lahir', 'tempat_lahir', $user) }}
        {{ form_input('Tgl. Lahir', 'tgl_lahir', $user, 'text', false, 'placeholder="DD-MM-YYYY"') }}
        {{ form_textarea('Alamat', 'alamat', $user) }}
        {{ form_input('Jenis Kelamin (L/P)', 'jenis_kelamin', $user) }}
        {{ form_input('Agama', 'agama', $user) }}
        {{ form_input('No. HP', 'no_hp', $user) }}
        {{ form_input('Telepon', 'telepon', $user) }}
        {{ form_input('NPWP', 'npwp', $user) }}
    </div>

    {{-- Kolom Tengah: Informasi Kepegawaian --}}
    <div class="md:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Kepegawaian</h3>
        {{ form_input('Golongan', 'golongan', $user) }}
        {{ form_input('TMT Golongan', 'tmt_gol', $user, 'text', false, 'placeholder="DD-MM-YYYY"') }}
        {{ form_input('Eselon', 'eselon', $user) }}
        {{ form_input('TMT Eselon', 'tmt_eselon', $user, 'text', false, 'placeholder="DD-MM-YYYY"') }}
        {{ form_input('Jenis Jabatan', 'jenis_jabatan', $user) }}
        {{ form_input('Grade', 'grade', $user) }}
        {{ form_input('Pendidikan Terakhir', 'pendidikan_terakhir', $user) }}
        {{ form_input('TMT CPNS', 'tmt_cpns', $user, 'text', false, 'placeholder="DD-MM-YYYY"') }}
        {{ form_input('TMT PNS', 'tmt_pns', $user, 'text', false, 'placeholder="DD-MM-YYYY"') }}
        {{ form_input('TMT Jabatan', 'tmt_jabatan', $user, 'text', false, 'placeholder="DD-MM-YYYY"') }}
    </div>

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
        <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $user->unit_id ?? '') }}">
        <div class="mb-4">
            <label for="jabatan_id" class="block font-semibold text-sm text-gray-700 mb-1">5. Jabatan</label>
            <select name="jabatan_id" id="jabatan_id" required class="block mt-1 w-full rounded-lg shadow-sm border-gray-300" disabled><option value="">-- Pilih Unit Kerja Terakhir --</option></select>
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
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const jabatanSelect = $('#jabatan_id');
    const unitIdInput = $('#unit_id');
    const unitSelects = $('.unit-select');

    const selectedUnitPath = @json($selectedUnitPath ?? []);
    const oldJabatanId = '{{ old('jabatan_id', optional($user->jabatan)->id ?? '') }}';

    function fetchAndPopulateJabatans(unitId, selectedId = null) {
        jabatanSelect.prop('disabled', true).html('<option value="">-- Memuat Jabatan... --</option>');
        if (!unitId) {
            jabatanSelect.html('<option value="">-- Pilih Unit Kerja Terakhir --</option>');
            return;
        }

        let url = `/api/units/${unitId}/vacant-jabatans`;
        @if ($user->exists)
            url += `?user_id={{ $user->id }}`;
        @endif

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                jabatanSelect.empty().append('<option value="">-- Pilih Jabatan --</option>');

                if (data.length > 0) {
                    $.each(data, function(key, jabatan) {
                        // Set the 'selected' property if the current jabatan id matches the old one
                        let isSelected = (jabatan.id == selectedId);
                        jabatanSelect.append(new Option(jabatan.name, jabatan.id, false, isSelected));
                    });
                }

                // After populating, if a selectedId was passed, ensure it's selected.
                // This is a fallback for cases where the initial selection might not have caught.
                if (selectedId) {
                    jabatanSelect.val(selectedId);
                }

                if (jabatanSelect.find('option').length <= 1) {
                    jabatanSelect.html('<option value="">-- Tidak ada jabatan tersedia --</option>');
                } else {
                    jabatanSelect.prop('disabled', false);
                }
            },
            error: function() {
                jabatanSelect.html('<option value="">-- Gagal Memuat Jabatan --</option>');
            }
        });
    }

    function resetSubsequentSelects(level) {
        for (let i = level; i < unitSelects.length; i++) {
            const select = $(unitSelects[i]);
            const placeholder = select.data('placeholder');
            select.empty().append(new Option(placeholder, '')).prop('disabled', true);
        }
        jabatanSelect.empty().append('<option value="">-- Pilih Unit Kerja Terakhir --</option>').prop('disabled', true);
    }

    unitSelects.on('change', function() {
        const selectedValue = $(this).val();
        const currentLevel = parseInt($(this).data('level'), 10);

        unitIdInput.val(selectedValue);
        resetSubsequentSelects(currentLevel);

        if (!selectedValue) {
            if (currentLevel > 1) {
                const prevSelect = $(unitSelects[currentLevel - 2]);
                unitIdInput.val(prevSelect.val());
            } else {
                unitIdInput.val('');
            }
            fetchAndPopulateJabatans(unitIdInput.val());
            return;
        }

        fetchAndPopulateJabatans(selectedValue, oldJabatanId);

        const nextLevel = currentLevel + 1;
        const nextSelect = $(`.unit-select[data-level='${nextLevel}']`);

        if (nextSelect.length) {
            nextSelect.prop('disabled', true).html('<option value="">-- Memuat... --</option>');
            $.ajax({
                url: `/units/${selectedValue}/children`,
                type: 'GET',
                success: function(data) {
                    const placeholder = nextSelect.data('placeholder');
                    nextSelect.empty().append(new Option(placeholder, ''));
                    if (data.length > 0) {
                        $.each(data, function(key, unit) {
                            nextSelect.append(new Option(unit.name, unit.id));
                        });
                        nextSelect.prop('disabled', false);
                    } else {
                        nextSelect.html(new Option('-- Tidak ada unit bawahan --', '')).prop('disabled', true);
                    }
                },
                error: function() {
                    nextSelect.html(new Option('-- Gagal memuat data --', '')).prop('disabled', true);
                }
            });
        }
    });

    function initializePath() {
        if (selectedUnitPath.length === 0) return;

        let currentPromise = $.Deferred().resolve().promise();
        selectedUnitPath.forEach((unitId, index) => {
            currentPromise = currentPromise.then(() => {
                return new Promise(resolve => {
                    const select = $(unitSelects[index]);
                    if(index > 0) {
                        const observer = new MutationObserver((mutationsList, obs) => {
                            for(const mutation of mutationsList) {
                                if (mutation.type === 'childList' && select.find('option').length > 1) {
                                    select.val(unitId);
                                    obs.disconnect();
                                    setTimeout(() => {
                                        select.trigger('change');
                                        resolve();
                                    }, 50);
                                    return;
                                }
                            }
                        });
                        observer.observe(select[0], { childList: true });
                    } else {
                        select.val(unitId).trigger('change');
                        resolve();
                    }
                });
            });
        });
    }

    initializePath();
});
</script>
@endpush
