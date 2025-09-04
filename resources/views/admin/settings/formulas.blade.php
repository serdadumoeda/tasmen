<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Rumus Perhitungan Kinerja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            {{-- Kolom Utama: Pengaturan Rumus --}}
                            <div class="lg:col-span-2 space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">Rumus Perhitungan</h3>
                                <div>
                                    <label for="iki_formula" class="block font-semibold text-sm text-gray-700 mb-1">Rumus Indeks Kinerja Individu (IKI)</label>
                                    <textarea name="iki_formula" id="iki_formula" rows="3" class="formula-input mt-1 block w-full rounded-lg shadow-sm border-gray-300 font-mono text-sm">{{ old('iki_formula', $settings['iki_formula'] ?? '') }}</textarea>
                                </div>
                                <div>
                                    <label for="nkf_formula_staf" class="block font-semibold text-sm text-gray-700 mb-1">Rumus Nilai Kinerja Final (NKF) - Staf</label>
                                    <textarea name="nkf_formula_staf" id="nkf_formula_staf" rows="2" class="formula-input mt-1 block w-full rounded-lg shadow-sm border-gray-300 font-mono text-sm">{{ old('nkf_formula_staf', $settings['nkf_formula_staf'] ?? '') }}</textarea>
                                </div>
                                <div>
                                    <label for="nkf_formula_pimpinan" class="block font-semibold text-sm text-gray-700 mb-1">Rumus Nilai Kinerja Final (NKF) - Pimpinan</label>
                                    <textarea name="nkf_formula_pimpinan" id="nkf_formula_pimpinan" rows="3" class="formula-input mt-1 block w-full rounded-lg shadow-sm border-gray-300 font-mono text-sm">{{ old('nkf_formula_pimpinan', $settings['nkf_formula_pimpinan'] ?? '') }}</textarea>
                                </div>

                                <h3 class="text-lg font-medium text-gray-900 pt-4 border-t">Parameter Kinerja</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="min_efficiency_factor" class="block font-semibold text-sm text-gray-700 mb-1">Min. Faktor Efisiensi</label>
                                        <input type="text" name="min_efficiency_factor" id="min_efficiency_factor" value="{{ old('min_efficiency_factor', $settings['min_efficiency_factor'] ?? '0.9') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300">
                                    </div>
                                    <div>
                                        <label for="max_efficiency_factor" class="block font-semibold text-sm text-gray-700 mb-1">Maks. Faktor Efisiensi</label>
                                        <input type="text" name="max_efficiency_factor" id="max_efficiency_factor" value="{{ old('max_efficiency_factor', $settings['max_efficiency_factor'] ?? '1.25') }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300">
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>

                            {{-- Kolom Samping: Variabel & Simulasi --}}
                            <div class="space-y-6">
                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h3 class="font-semibold text-gray-800 mb-2">Variabel Tersedia</h3>
                                    <ul class="space-y-1 text-xs text-gray-600 font-mono">
                                        <li><span class="font-semibold">base_score</span>: Rata-rata progres tugas</li>
                                        <li><span class="font-semibold">efficiency_factor</span>: Efisiensi jam kerja</li>
                                        <li><span class="font-semibold">capped_efficiency_factor</span>: Efisiensi yg dibatasi</li>
                                        <li><span class="font-semibold">individual_score</span>: Skor IKI</li>
                                        <li><span class="font-semibold">managerial_score</span>: Rata-rata NKF bawahan</li>
                                        <li><span class="font-semibold">weight</span>: Bobot manajerial</li>
                                    </ul>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h3 class="font-semibold text-gray-800 mb-2">Simulasi Rumus</h3>
                                    <p class="text-xs text-gray-600 mb-4">Uji coba rumus Anda dengan nilai contoh. Pilih rumus yang ingin diuji.</p>
                                    <div id="simulation-container" class="space-y-4">
                                        <select id="formula_to_simulate" class="block w-full rounded-lg shadow-sm border-gray-300 text-sm">
                                            <option value="iki_formula">Rumus IKI</option>
                                            <option value="nkf_formula_staf">Rumus NKF Staf</option>
                                            <option value="nkf_formula_pimpinan">Rumus NKF Pimpinan</option>
                                        </select>
                                        <div id="simulation-inputs" class="space-y-2"></div>
                                        <button type="button" id="simulate_button" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            Simulasikan
                                        </button>
                                        <div id="simulation-result" class="mt-4 p-3 bg-white rounded-lg text-center font-mono text-lg font-bold">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const formulaSelect = document.getElementById('formula_to_simulate');
        const inputsContainer = document.getElementById('simulation-inputs');
        const simulateButton = document.getElementById('simulate_button');
        const resultContainer = document.getElementById('simulation-result');

        const variableSets = {
            iki_formula: ['base_score', 'efficiency_factor', 'capped_efficiency_factor'],
            nkf_formula_staf: ['individual_score'],
            nkf_formula_pimpinan: ['individual_score', 'managerial_score', 'weight']
        };

        function renderInputs() {
            const selectedFormulaKey = formulaSelect.value;
            const variables = variableSets[selectedFormulaKey];
            inputsContainer.innerHTML = '';

            variables.forEach(variable => {
                const div = document.createElement('div');
                const label = document.createElement('label');
                label.htmlFor = `sim_${variable}`;
                label.className = 'text-xs text-gray-600';
                label.textContent = variable;

                const input = document.createElement('input');
                input.type = 'number';
                input.step = '0.01';
                input.id = `sim_${variable}`;
                input.name = variable;
                input.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm';
                input.placeholder = 'Contoh: 1.0';

                div.appendChild(label);
                div.appendChild(input);
                inputsContainer.appendChild(div);
            });
        }

        async function runSimulation() {
            const formulaKey = formulaSelect.value;
            const formula = document.getElementById(formulaKey).value;

            const variables = {};
            const inputs = inputsContainer.querySelectorAll('input');
            let allInputsFilled = true;
            inputs.forEach(input => {
                if (!input.value) allInputsFilled = false;
                variables[input.name] = parseFloat(input.value) || 0;
            });

            if (!allInputsFilled) {
                resultContainer.textContent = 'Isi semua nilai';
                resultContainer.classList.add('text-red-500');
                return;
            }

            resultContainer.textContent = 'Menghitung...';
            resultContainer.classList.remove('text-red-500', 'text-green-500');

            try {
                const response = await fetch("{{ route('admin.settings.simulate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ formula, variables })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Terjadi kesalahan');
                }

                resultContainer.textContent = `Hasil: ${data.result.toFixed(4)}`;
                resultContainer.classList.add('text-green-500');

            } catch (error) {
                resultContainer.textContent = error.message;
                resultContainer.classList.add('text-red-500');
            }
        }

        formulaSelect.addEventListener('change', renderInputs);
        simulateButton.addEventListener('click', runSimulation);

        // Initial render
        renderInputs();
    });
    </script>
    @endpush
</x-app-layout>
