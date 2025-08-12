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
            <label for="unit_id" class="block font-semibold text-sm text-gray-700 mb-1">1. Pilih Unit Kerja</label>
            <select name="unit_id" id="unit_id" required class="select2-searchable block mt-1 w-full rounded-lg shadow-sm">
                <option value="">-- Pilih Unit --</option>
                @foreach($units as $unitOption)
                    <option value="{{ $unitOption->id }}" @selected(old('unit_id', $user->unit_id ?? '') == $unitOption->id)>{{ $unitOption->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label for="jabatan_id" class="block font-semibold text-sm text-gray-700 mb-1">2. Pilih Jabatan Tersedia</label>
            <select name="jabatan_id" id="jabatan_id" required class="select2-searchable block mt-1 w-full rounded-lg shadow-sm" disabled>
                <option value="">-- Pilih Unit Dahulu --</option>
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
        const unitSelect = $('#unit_id');
        const jabatanSelect = $('#jabatan_id');

        @isset($user)
            const oldJabatanId = '{{ old('jabatan_id', optional($user->jabatan)->id ?? '') }}';
        @else
            const oldJabatanId = '{{ old('jabatan_id', '') }}';
        @endisset

        function fetchAndPopulateJabatans(unitId, selectedId = null) {
            jabatanSelect.prop('disabled', true).html('<option value="">-- Memuat... --</option>');

            if (!unitId) {
                jabatanSelect.html('<option value="">-- Pilih Unit Dahulu --</option>').trigger('change');
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
                        jabatanSelect.val(selectedId);
                    }

                    jabatanSelect.prop('disabled', false).trigger('change');
                },
                error: function() {
                    jabatanSelect.html('<option value="">-- Gagal Memuat --</option>').trigger('change');
                }
            });
        }

        unitSelect.on('change', function() {
            fetchAndPopulateJabatans($(this).val());
        });

        // Initial load if a unit is already selected
        if (unitSelect.val()) {
            fetchAndPopulateJabatans(unitSelect.val(), oldJabatanId);
        }
    });
</script>
@endpush