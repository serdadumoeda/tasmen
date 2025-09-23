<section>
    @php
        $formatDate = function ($value) {
            return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
        };
    @endphp

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-8">
        @csrf
        @method('patch')

        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <strong class="font-semibold">Periksa kembali data yang Anda isi:</strong>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Akun</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">
                                <i class="fas fa-user mr-2 text-gray-500"></i> {{ __('Nama') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('name')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block font-semibold text-sm text-gray-700 mb-1">
                                <i class="fas fa-envelope mr-2 text-gray-500"></i> {{ __('Email') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('email')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-r-lg shadow-sm">
                                    <p class="text-sm flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-yellow-500"></i> {{ __('Alamat email Anda belum diverifikasi.') }}
                                    </p>
                                    <button form="send-verification" class="underline text-sm text-yellow-700 hover:text-yellow-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 mt-2 inline-block">
                                        {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label for="nik" class="block font-semibold text-sm text-gray-700 mb-1">
                                <i class="fas fa-id-card mr-2 text-gray-500"></i> {{ __('NIK') }}
                            </label>
                            <input id="nik" name="nik" type="text" value="{{ old('nik', $user->nik) }}" autocomplete="off" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('nik') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('nik')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="nip" class="block font-semibold text-sm text-gray-700 mb-1">NIP</label>
                            <input id="nip" name="nip" type="text" value="{{ old('nip', $user->nip) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('nip') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('nip')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontak</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="no_hp" class="block font-semibold text-sm text-gray-700 mb-1">No. HP</label>
                            <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp', $user->no_hp) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('no_hp') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('no_hp')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="telepon" class="block font-semibold text-sm text-gray-700 mb-1">Telepon</label>
                            <input id="telepon" name="telepon" type="text" value="{{ old('telepon', $user->telepon) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('telepon') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('telepon')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="npwp" class="block font-semibold text-sm text-gray-700 mb-1">NPWP</label>
                            <input id="npwp" name="npwp" type="text" value="{{ old('npwp', $user->npwp) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('npwp') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('npwp')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pribadi</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="tempat_lahir" class="block font-semibold text-sm text-gray-700 mb-1">Tempat Lahir</label>
                            <input id="tempat_lahir" name="tempat_lahir" type="text" value="{{ old('tempat_lahir', $user->tempat_lahir) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('tempat_lahir') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('tempat_lahir')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tgl_lahir" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Lahir</label>
                            <input id="tgl_lahir" name="tgl_lahir" type="date" value="{{ old('tgl_lahir', $formatDate($user->tgl_lahir)) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('tgl_lahir') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('tgl_lahir')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="jenis_kelamin" class="block font-semibold text-sm text-gray-700 mb-1">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('jenis_kelamin') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="">-- Pilih --</option>
                                <option value="L" @selected(old('jenis_kelamin', $user->jenis_kelamin) === 'L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin', $user->jenis_kelamin) === 'P')>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="agama" class="block font-semibold text-sm text-gray-700 mb-1">Agama</label>
                            <input id="agama" name="agama" type="text" value="{{ old('agama', $user->agama) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('agama') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('agama')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="alamat" class="block font-semibold text-sm text-gray-700 mb-1">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="4" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('alamat') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendidikan & Jabatan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pendidikan_terakhir" class="block font-semibold text-sm text-gray-700 mb-1">Pendidikan Terakhir</label>
                            <input id="pendidikan_terakhir" name="pendidikan_terakhir" type="text" value="{{ old('pendidikan_terakhir', $user->pendidikan_terakhir) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="pendidikan_jurusan" class="block font-semibold text-sm text-gray-700 mb-1">Jurusan</label>
                            <input id="pendidikan_jurusan" name="pendidikan_jurusan" type="text" value="{{ old('pendidikan_jurusan', $user->pendidikan_jurusan) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="pendidikan_universitas" class="block font-semibold text-sm text-gray-700 mb-1">Universitas</label>
                            <input id="pendidikan_universitas" name="pendidikan_universitas" type="text" value="{{ old('pendidikan_universitas', $user->pendidikan_universitas) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="jenis_jabatan" class="block font-semibold text-sm text-gray-700 mb-1">Jenis Jabatan</label>
                            <input id="jenis_jabatan" name="jenis_jabatan" type="text" value="{{ old('jenis_jabatan', $user->jenis_jabatan) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kepegawaian</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="golongan" class="block font-semibold text-sm text-gray-700 mb-1">Golongan</label>
                            <input id="golongan" name="golongan" type="text" value="{{ old('golongan', $user->golongan) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="eselon" class="block font-semibold text-sm text-gray-700 mb-1">Eselon</label>
                            <input id="eselon" name="eselon" type="text" value="{{ old('eselon', $user->eselon) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="tmt_eselon" class="block font-semibold text-sm text-gray-700 mb-1">TMT Eselon</label>
                            <input id="tmt_eselon" name="tmt_eselon" type="date" value="{{ old('tmt_eselon', $formatDate($user->tmt_eselon)) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="grade" class="block font-semibold text-sm text-gray-700 mb-1">Grade</label>
                            <input id="grade" name="grade" type="text" value="{{ old('grade', $user->grade) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="tmt_cpns" class="block font-semibold text-sm text-gray-700 mb-1">TMT CPNS</label>
                            <input id="tmt_cpns" name="tmt_cpns" type="date" value="{{ old('tmt_cpns', $formatDate($user->tmt_cpns)) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label for="tmt_pns" class="block font-semibold text-sm text-gray-700 mb-1">TMT PNS</label>
                            <input id="tmt_pns" name="tmt_pns" type="date" value="{{ old('tmt_pns', $formatDate($user->tmt_pns)) }}" class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Unit Kerja & Jabatan</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="eselon_i" class="block font-semibold text-sm text-gray-700 mb-1">1. Unit Eselon I</label>
                            <select id="eselon_i" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="1" data-placeholder="-- Pilih Unit Eselon I --">
                                <option value="">-- Pilih Unit Eselon I --</option>
                                @foreach($eselonIUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="eselon_ii" class="block font-semibold text-sm text-gray-700 mb-1">2. Unit Eselon II</label>
                            <select id="eselon_ii" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="2" data-placeholder="-- Pilih Unit Eselon II --" disabled>
                                <option value="">-- Pilih Unit Eselon I Dahulu --</option>
                            </select>
                        </div>
                        <div>
                            <label for="koordinator" class="block font-semibold text-sm text-gray-700 mb-1">3. Koordinator</label>
                            <select id="koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="3" data-placeholder="-- Pilih Koordinator --" disabled>
                                <option value="">-- Pilih Unit Eselon II Dahulu --</option>
                            </select>
                        </div>
                        <div>
                            <label for="sub_koordinator" class="block font-semibold text-sm text-gray-700 mb-1">4. Sub Koordinator</label>
                            <select id="sub_koordinator" class="unit-select block mt-1 w-full rounded-lg shadow-sm border-gray-300" data-level="4" data-placeholder="-- Pilih Sub Koordinator --" disabled>
                                <option value="">-- Pilih Koordinator Dahulu --</option>
                            </select>
                        </div>

                        <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $user->unit_id) }}">
                        @error('unit_id')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror

                        <div>
                            <label for="jabatan_name" class="block font-semibold text-sm text-gray-700 mb-1">5. Nama Jabatan <span class="text-red-500">*</span></label>
                            <input id="jabatan_name" name="jabatan_name" type="text" value="{{ old('jabatan_name', optional($user->jabatan)->name) }}" required class="mt-1 block w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                            <p class="text-xs text-gray-500 mt-1">Jabatan akan dibuat atau diperbarui berdasarkan nama yang Anda masukkan.</p>
                            @error('jabatan_name')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="atasan_id" class="block font-semibold text-sm text-gray-700 mb-1">Atasan Langsung</label>
                            <select name="atasan_id" id="atasan_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('atasan_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="">-- Tidak ada --</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" @selected(old('atasan_id', $user->atasan_id) == $supervisor->id)>{{ $supervisor->name }}</option>
                                @endforeach
                            </select>
                            @error('atasan_id')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 mt-8 pt-6 border-t border-gray-200">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-save mr-2"></i> {{ __('Simpan') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 flex items-center"
                >
                    <i class="fas fa-check-circle mr-2"></i> {{ __('Disimpan.') }}
                </p>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const unitSelects = Array.from(document.querySelectorAll('.unit-select'));
        const unitIdInput = document.getElementById('unit_id');
        const selectedPath = @json($selectedUnitPath ?? []);

        function resetNext(level) {
            for (let i = level; i < unitSelects.length; i++) {
                const select = unitSelects[i];
                const placeholder = select.dataset.placeholder || '-- Pilih --';
                select.innerHTML = `<option value="">${placeholder}</option>`;
                select.disabled = i !== 0;
            }
        }

        function fetchChildren(parentId, select) {
            if (!select) return;
            select.disabled = true;
            select.innerHTML = '<option value="">-- Memuat... --</option>';

            window.axios.get(`/api/units/${parentId}/children`)
                .then(({ data }) => {
                    const placeholder = select.dataset.placeholder || '-- Pilih --';
                    select.innerHTML = `<option value="">${placeholder}</option>`;
                    if (Array.isArray(data) && data.length) {
                        data.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = unit.name;
                            select.appendChild(option);
                        });
                        select.disabled = false;
                    } else {
                        select.innerHTML = '<option value="">-- Tidak ada unit bawahan --</option>';
                        select.disabled = true;
                    }
                })
                .catch(() => {
                    select.innerHTML = '<option value="">-- Gagal memuat data --</option>';
                    select.disabled = true;
                });
        }

        unitSelects.forEach(select => {
            select.addEventListener('change', function (event) {
                const currentLevel = parseInt(select.dataset.level, 10);
                const selectedValue = event.target.value;

                unitIdInput.value = selectedValue;
                resetNext(currentLevel);

                if (!selectedValue) {
                    if (currentLevel > 1) {
                        const previous = unitSelects[currentLevel - 2];
                        unitIdInput.value = previous ? previous.value : '';
                    } else {
                        unitIdInput.value = '';
                    }
                    return;
                }

                const nextSelect = unitSelects.find(el => parseInt(el.dataset.level, 10) === currentLevel + 1);
                if (nextSelect) {
                    fetchChildren(selectedValue, nextSelect);
                }
            });
        });

        function initialisePath() {
            if (!selectedPath.length) {
                // Preselect current unit in first dropdown if available
                const firstSelect = unitSelects[0];
                const currentUnitId = unitIdInput.value;
                if (firstSelect && currentUnitId) {
                    firstSelect.value = currentUnitId;
                }
                return;
            }

            resetNext(1);

            let sequence = Promise.resolve();
            selectedPath.forEach((unitId, index) => {
                sequence = sequence.then(() => {
                    const select = unitSelects[index];
                    if (!select) return Promise.resolve();

                    select.value = unitId;
                    unitIdInput.value = unitId;

                    const nextSelect = unitSelects[index + 1];
                    if (!nextSelect) {
                        return Promise.resolve();
                    }

                    return new Promise(resolve => {
                        fetchChildren(unitId, nextSelect);
                        const observer = new MutationObserver((mutations, obs) => {
                            for (const mutation of mutations) {
                                if (mutation.type === 'childList' && nextSelect.options.length > 1) {
                                    nextSelect.value = selectedPath[index + 1] || '';
                                    nextSelect.dispatchEvent(new Event('change'));
                                    obs.disconnect();
                                    resolve();
                                    return;
                                }
                            }
                        });
                        observer.observe(nextSelect, { childList: true });
                    });
                });
            });
        }

        // Ensure axios exists, fallback to fetch if not
        if (typeof window.axios === 'undefined') {
            window.axios = {
                get: (url) => fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(response => response.json().then(data => ({ data })))
            };
        }

        initialisePath();
    });
</script>
@endpush
