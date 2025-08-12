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

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Kolom Kiri --}}
    <div>
        <div class="mb-6">
            <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">Nama Lengkap</label>
            <input id="name" class="block mt-1 w-full rounded-lg shadow-sm" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required autofocus />
        </div>

        <div class="mb-6">
            <label for="email" class="block font-semibold text-sm text-gray-700 mb-1">Email</label>
            <input id="email" class="block mt-1 w-full rounded-lg shadow-sm" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required />
        </div>

        <div class="mb-6">
            <label for="eselon_i" class="block font-semibold text-sm text-gray-700 mb-1">1. Unit Eselon I</label>
            <select id="eselon_i" class="unit-select block mt-1 w-full rounded-lg shadow-sm" data-level="1" data-placeholder="-- Pilih Unit Eselon I --">
                <option value="">-- Pilih Unit Eselon I --</option>
                @foreach($eselonIUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label for="eselon_ii" class="block font-semibold text-sm text-gray-700 mb-1">2. Unit Eselon II</label>
            <select id="eselon_ii" class="unit-select block mt-1 w-full rounded-lg shadow-sm" data-level="2" data-placeholder="-- Pilih Unit Eselon II --" disabled>
                <option value="">-- Pilih Unit Eselon I Dahulu --</option>
            </select>
        </div>

        <div class="mb-6">
            <label for="koordinator" class="block font-semibold text-sm text-gray-700 mb-1">3. Koordinator</label>
            <select id="koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm" data-level="3" data-placeholder="-- Pilih Koordinator --" disabled>
                <option value="">-- Pilih Unit Eselon II Dahulu --</option>
            </select>
        </div>

        <div class="mb-6">
            <label for="sub_koordinator" class="block font-semibold text-sm text-gray-700 mb-1">4. Sub Koordinator</label>
            <select id="sub_koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm" data-level="4" data-placeholder="-- Pilih Sub Koordinator --" disabled>
                <option value="">-- Pilih Koordinator Dahulu --</option>
            </select>
        </div>

        <!-- Hidden input to store the final selected unit_id -->
        <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $user->unit_id ?? '') }}">

        <div class="mb-6">
            <label for="jabatan_id" class="block font-semibold text-sm text-gray-700 mb-1">5. Pilih Jabatan Tersedia</label>
            <select name="jabatan_id" id="jabatan_id" required class="select2-searchable block mt-1 w-full rounded-lg shadow-sm" disabled>
                <option value="">-- Pilih Unit Kerja Terakhir --</option>
            </select>
        </div>
    </div>

    {{-- Kolom Kanan --}}
    <div>
        <div class="mb-6">
            <label for="atasan_id" class="block font-semibold text-sm text-gray-700 mb-1">Pilih Atasan Langsung</label>
            <select name="atasan_id" id="atasan_id" class="select2-searchable block mt-1 w-full rounded-lg shadow-sm">
                 <option value="">-- Tidak ada --</option>
                 @foreach($supervisors as $supervisor)
                    <option value="{{ $supervisor->id }}" @selected(old('atasan_id', $user->atasan_id ?? '') == $supervisor->id)>{{ $supervisor->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label for="password" class="block font-semibold text-sm text-gray-700 mb-1">Password @if(!isset($user))<span class="text-red-500">*</span>@endif</label>
            <input id="password" class="block mt-1 w-full rounded-lg shadow-sm" type="password" name="password" @if(!isset($user)) required @endif />
            @if(isset($user))<p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah.</p>@endif
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block font-semibold text-sm text-gray-700 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" class="block mt-1 w-full rounded-lg shadow-sm" type="password" name="password_confirmation" />
        </div>

        <div class="mb-6">
            <label for="status" class="block font-semibold text-sm text-gray-700 mb-1">Status</label>
            <select name="status" id="status" required class="block mt-1 w-full rounded-lg shadow-sm">
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

    // Path for pre-selection on edit form
    const selectedUnitPath = @json($selectedUnitPath ?? []);

    @isset($user)
        const oldJabatanId = '{{ old('jabatan_id', optional($user->jabatan)->id ?? '') }}';
    @else
        const oldJabatanId = '{{ old('jabatan_id', '') }}';
    @endisset

    function fetchAndPopulateJabatans(unitId, selectedId = null) {
        jabatanSelect.prop('disabled', true).html('<option value="">-- Memuat... --</option>');

        if (!unitId) {
            jabatanSelect.html('<option value="">-- Pilih Unit Kerja Terakhir --</option>').trigger('change');
            return;
        }

        $.ajax({
            url: `/api/units/${unitId}/vacant-jabatans`,
            type: 'GET',
            success: function(data) {
                jabatanSelect.empty().append('<option value="">-- Pilih Jabatan --</option>');

                if (data.length === 0) {
                    jabatanSelect.html('<option value="">-- Tidak ada jabatan kosong --</option>');
                } else {
                    $.each(data, function(key, jabatan) {
                        jabatanSelect.append(new Option(jabatan.name, jabatan.id, false, false));
                    });
                }

                if (selectedId) {
                    jabatanSelect.val(selectedId).trigger('change');
                }

                jabatanSelect.prop('disabled', false);
            },
            error: function() {
                jabatanSelect.html('<option value="">-- Gagal Memuat Jabatan --</option>');
            }
        });
    }

    function resetSubsequentSelects(level) {
        for (let i = level; i <= unitSelects.length; i++) {
            const select = $(unitSelects[i]);
            const placeholder = select.data('placeholder') || '-- Pilih --';
            select.empty().append(new Option(placeholder, '')).prop('disabled', true).val('').trigger('change');
        }
        jabatanSelect.empty().append('<option value="">-- Pilih Unit Kerja Terakhir --</option>').prop('disabled', true).val('').trigger('change');
    }

    unitSelects.on('change', function() {
        const selectedValue = $(this).val();
        const currentLevel = parseInt($(this).data('level'), 10);

        unitIdInput.val(selectedValue); // Update hidden input
        resetSubsequentSelects(currentLevel);

        if (!selectedValue) {
            // If placeholder is selected, ensure the hidden unit_id is cleared
            // or set to the value of the previous dropdown.
            if(currentLevel > 1) {
                const prevSelect = $(unitSelects[currentLevel - 2]);
                unitIdInput.val(prevSelect.val());
            } else {
                unitIdInput.val('');
            }
            fetchAndPopulateJabatans(unitIdInput.val());
            return;
        }

        fetchAndPopulateJabatans(selectedValue);

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
        if (selectedUnitPath.length === 0) {
            // On create form, if there's an old unit_id (e.g., validation fails),
            // we should still try to fetch jabatans for it.
            if(unitIdInput.val()) {
                fetchAndPopulateJabatans(unitIdInput.val(), oldJabatanId);
            }
            return;
        };

        let currentPromise = $.Deferred().resolve().promise();

        selectedUnitPath.forEach((unitId, index) => {
            currentPromise = currentPromise.then(() => {
                return new Promise(resolve => {
                    const select = $(unitSelects[index]);
                    if(index > 0) {
                        // For selects other than the first one, wait for them to be populated
                        // This uses a MutationObserver to wait for the options to be added
                        const observer = new MutationObserver((mutationsList, obs) => {
                            for(const mutation of mutationsList) {
                                if (mutation.type === 'childList') {
                                    select.val(unitId).trigger('change');
                                    obs.disconnect(); // Clean up the observer
                                    resolve();
                                    return;
                                }
                            }
                        });
                        observer.observe(select[0], { childList: true });
                    } else {
                        // First select is already populated, just trigger it
                        select.val(unitId).trigger('change');
                        resolve();
                    }
                });
            });
        });

        currentPromise.then(() => {
            // After the chain is complete, fetch the jabatans for the final unit
            const finalUnitId = selectedUnitPath[selectedUnitPath.length - 1];
            fetchAndPopulateJabatans(finalUnitId, oldJabatanId);
        });
    }

    initializePath();
});
</script>
@endpush