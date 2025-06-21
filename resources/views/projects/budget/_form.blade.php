@if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 p-4">...</div>
@endif

<div class="space-y-4">
    <div>
        <label for="category" class="block font-medium text-sm text-gray-700">Kategori Anggaran</label>
        <select name="category" id="category" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
            @php
                $categories = ['HONORARIUM' => 'Honorarium & Uang Harian', 'PERJALANAN_DINAS' => 'Perjalanan Dinas', 'PENGADAAN_BARANG_JASA' => 'Pengadaan Barang/Jasa', 'LAINNYA' => 'Lainnya'];
            @endphp
            <option value="">-- Pilih Kategori --</option>
            @foreach ($categories as $key => $value)
                <option value="{{ $key }}" @selected(old('category', $budgetItem->category ?? '') == $key)>{{ $value }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="item_name" class="block font-medium text-sm text-gray-700">Uraian / Nama Item</label>
        <input type="text" name="item_name" id="item_name" value="{{ old('item_name', $budgetItem->item_name ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="quantity" class="block font-medium text-sm text-gray-700">Kuantitas</label>
            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $budgetItem->quantity ?? 1) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        </div>
        <div>
            <label for="frequency" class="block font-medium text-sm text-gray-700">Frekuensi</label>
            <input type="number" name="frequency" id="frequency" value="{{ old('frequency', $budgetItem->frequency ?? 1) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        </div>
        <div>
            <label for="unit_price" class="block font-medium text-sm text-gray-700">Harga Satuan (Rp)</label>
            <input type="number" step="0.01" name="unit_price" id="unit_price" value="{{ old('unit_price', $budgetItem->unit_price ?? 0) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
        </div>
    </div>
    <div>
        <label for="description" class="block font-medium text-sm text-gray-700">Keterangan</label>
        <textarea name="description" id="description" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description', $budgetItem->description ?? '') }}</textarea>
    </div>
</div>
<div class="flex items-center justify-end mt-6">
    <a href="{{ route('projects.budget-items.index', $project) }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
        {{ $budgetItem->exists ? 'Update Item' : 'Simpan Item' }}
    </button>
</div>