<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lengkapi Profil Anda') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8">
                    <h3 class="text-2xl font-semibold text-gray-900">Selamat Datang, {{ Auth::user()->name }}!</h3>
                    <p class="mt-2 text-gray-600">
                        Untuk melanjutkan, silakan pilih unit kerja dan jabatan Anda dari daftar yang tersedia di bawah ini.
                        Ini akan membantu kami menyesuaikan pengalaman Anda di dalam sistem.
                    </p>

                    <form action="{{ route('profile.complete.store') }}" method="POST" class="mt-8">
                        @csrf

                        {{-- We can reuse the form fields from the user admin panel --}}
                        {{-- We only need the unit and jabatan selection part --}}
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Unit Kerja & Jabatan</h3>
                            <div class="mb-4">
                                <label for="induk" class="block font-semibold text-sm text-gray-700 mb-1">1. Induk Organisasi</label>
                                <select id="induk" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="0" data-placeholder="-- Pilih Induk Organisasi --">
                                    <option value="">-- Pilih Induk Organisasi --</option>
                                    @foreach($eselonIUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="eselon_i" class="block font-semibold text-sm text-gray-700 mb-1">2. Unit Eselon I</label>
                                <select id="eselon_i" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="1" data-placeholder="-- Pilih Unit Eselon I --" disabled><option value="">-- Pilih Induk Organisasi Dahulu --</option></select>
                            </div>
                            <div class="mb-4">
                                <label for="eselon_ii" class="block font-semibold text-sm text-gray-700 mb-1">3. Unit Eselon II</label>
                                <select id="eselon_ii" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="2" data-placeholder="-- Pilih Unit Eselon II --" disabled><option value="">-- Pilih Unit Eselon I Dahulu --</option></select>
                            </div>
                            <div class="mb-4">
                                <label for="koordinator" class="block font-semibold text-sm text-gray-700 mb-1">4. Koordinator</label>
                                <select id="koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="3" data-placeholder="-- Pilih Koordinator --" disabled><option value="">-- Pilih Unit Eselon II Dahulu --</option></select>
                            </div>
                            <div class="mb-4">
                                <label for="sub_koordinator" class="block font-semibold text-sm text-gray-700 mb-1">5. Sub Koordinator</label>
                                <select id="sub_koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="4" data-placeholder="-- Pilih Sub Koordinator --" disabled><option value="">-- Pilih Koordinator Dahulu --</option></select>
                            </div>
                            <div class="mb-4">
                                <label for="jabatan_name" class="block font-semibold text-sm text-gray-700 mb-1">6. Nama Jabatan <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" name="jabatan_name" id="jabatan_name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('jabatan_name') }}" required>
                                <p class="text-xs text-gray-500 mt-1">Masukkan nama jabatan Anda saat ini.</p>
                                <x-input-error :messages="$errors->get('jabatan_name')" class="mt-2" />
                            </div>
                            <input type="hidden" name="unit_id" id="unit_id" value="">
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <button type="submit" id="submit_button" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105" disabled>
                                <i class="fas fa-save mr-2"></i> {{ __('Simpan & Lanjutkan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        const unitIdInput = $('#unit_id');
        // Correctly select all unit dropdowns in order of their level
        const unitSelects = $('.unit-select').sort(function(a, b) {
            return parseInt($(a).data('level')) - parseInt($(b).data('level'));
        });
        const submitButton = $('#submit_button');
        const jabatanNameInput = $('#jabatan_name');

        function checkFormValidity() {
            const isUnitSelected = unitIdInput.val() !== '';
            const isJabatanFilled = jabatanNameInput.val().trim() !== '';
            submitButton.prop('disabled', !(isUnitSelected && isJabatanFilled));
        }

        function resetSubsequentSelects(level) {
            // Start resetting from the level *after* the one that was changed.
            for (let i = level + 1; i < unitSelects.length; i++) {
                const select = $(unitSelects[i]);
                const placeholder = select.data('placeholder');
                select.empty().append(new Option(placeholder, '')).prop('disabled', true);
            }
        }

        unitSelects.on('change', function() {
            const selectedValue = $(this).val();
            const currentLevel = parseInt($(this).data('level'), 10);

            // Always update the hidden input with the latest valid selection
            unitIdInput.val(selectedValue);

            // Reset all dropdowns that come after the current one
            resetSubsequentSelects(currentLevel);

            if (!selectedValue) {
                // If a select is cleared, the effective unit is the parent's value.
                if (currentLevel > 0) {
                    const prevSelect = $(`.unit-select[data-level='${currentLevel - 1}']`);
                    unitIdInput.val(prevSelect.val());
                } else {
                    unitIdInput.val(''); // Cleared the very first dropdown.
                }
                checkFormValidity();
                return;
            }

            const nextLevel = currentLevel + 1;
            const nextSelect = $(`.unit-select[data-level='${nextLevel}']`);

            if (nextSelect.length > 0) {
                nextSelect.prop('disabled', true).html('<option value="">-- Memuat... --</option>');
                $.ajax({
                    url: `/api/units/${selectedValue}/children`,
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
                            // If no children, keep the parent unit as the selected one and disable the next dropdown.
                            nextSelect.html(new Option('-- Tidak ada unit bawahan --', '')).prop('disabled', true);
                        }
                    },
                    error: function() {
                        nextSelect.html(new Option('-- Gagal memuat data --', '')).prop('disabled', true);
                    }
                });
            }

            // Always check validity after a change
            checkFormValidity();
        });

        jabatanNameInput.on('input', function() {
            checkFormValidity();
        });

        // Initial check on page load.
        checkFormValidity();
    });
    </script>
    @endpush
</x-app-layout>
