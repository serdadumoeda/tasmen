@csrf
<div class="space-y-4">
    <div>
        <label for="label" class="block text-sm font-medium text-gray-700">Label</label>
        <input type="text" name="label" id="label" value="{{ old('label', $role->label ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <p class="text-xs text-gray-500 mt-1">Nama yang akan ditampilkan kepada pengguna (misal: "Eselon I").</p>
        @error('label')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama (System)</label>
        <input type="text" name="name" id="name" value="{{ old('name', $role->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <p class="text-xs text-gray-500 mt-1">Pengenal unik untuk sistem (slug, huruf kecil, tanpa spasi, misal: "eselon_i").</p>
        @error('name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="managerial_weight" class="block text-sm font-medium text-gray-700">Bobot Manajerial</label>
        <input type="number" step="0.01" min="0" max="1" name="managerial_weight" id="managerial_weight" value="{{ old('managerial_weight', $role->managerial_weight ?? '0.00') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <p class="text-xs text-gray-500 mt-1">Angka antara 0.0 dan 1.0 untuk perhitungan NKF. Staf harus 0.</p>
        @error('managerial_weight')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex justify-end mt-6">
    <a href="{{ route('admin.roles.index') }}" class="mr-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
        Batal
    </a>
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
        Simpan
    </button>
</div>
