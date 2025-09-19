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
                                <label for="jabatan_name" class="block font-semibold text-sm text-gray-700 mb-1">5. Nama Jabatan Anda <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" name="jabatan_name" id="jabatan_name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300" placeholder="Contoh: Analis Hukum Ahli Madya" required disabled>
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
    document.addEventListener('DOMContentLoaded', () => {
        const unitSelects = document.querySelectorAll('.unit-select');
        const unitIdInput = document.getElementById('unit_id');
        const jabatanNameInput = document.getElementById('jabatan_name');
        const submitButton = document.getElementById('submit_button');

        const checkFormValidity = () => {
            const unitIsSelected = unitIdInput.value !== '';
            const jabatanIsFilled = jabatanNameInput.value.trim() !== '';
            submitButton.disabled = !(unitIsSelected && jabatanIsFilled);
        };

        const handleUnitSelectChange = async (event) => {
            const selectEl = event.target;
            const selectedUnitId = selectEl.value;
            const level = parseInt(selectEl.dataset.level, 10);
            let finalUnitId = '';

            // Reset subsequent dropdowns
            for (let i = level; i < unitSelects.length; i++) {
                const currentSelect = unitSelects[i];
                currentSelect.innerHTML = `<option value="">${currentSelect.dataset.placeholder}</option>`;
                currentSelect.disabled = true;
            }
            jabatanNameInput.disabled = true;

            if (selectedUnitId) {
                finalUnitId = selectedUnitId;
            } else if (level > 1) {
                finalUnitId = unitSelects[level - 2].value;
            }

            unitIdInput.value = finalUnitId;
            jabatanNameInput.disabled = !finalUnitId;
            checkFormValidity();

            if (selectedUnitId) {
                const nextLevel = level + 1;
                const nextSelect = document.querySelector(`.unit-select[data-level='${nextLevel}']`);
                if (nextSelect) {
                    nextSelect.disabled = true;
                    nextSelect.innerHTML = `<option value="">-- Memuat... --</option>`;

                    try {
                        const response = await fetch(`/api/units/${selectedUnitId}/children`);
                        const children = await response.json();

                        nextSelect.innerHTML = `<option value="">${nextSelect.dataset.placeholder}</option>`;
                        if (children.length > 0) {
                            children.forEach(item => nextSelect.add(new Option(item.name, item.id)));
                            nextSelect.disabled = false;
                        } else {
                            nextSelect.innerHTML = `<option value="">-- Tidak ada unit bawahan --</option>`;
                        }
                    } catch (e) {
                        console.error("Fetch error:", e);
                        nextSelect.innerHTML = `<option value="">-- Gagal memuat data --</option>`;
                    }
                }
            }
        };

        unitSelects.forEach(selectEl => {
            selectEl.addEventListener('change', handleUnitSelectChange);
        });

        jabatanNameInput.addEventListener('input', checkFormValidity);
    });
    </script>
    @endpush
</x-app-layout>
