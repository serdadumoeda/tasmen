@php $level = $level ?? 0; @endphp

<div x-data="{ open: true }" class="bg-gray-100 rounded-lg border border-gray-200" style="margin-left: {{ $level * 20 }}px;">
    {{-- Baris Judul Unit (Trigger untuk Accordion) --}}
    <div class="p-4 flex items-center justify-between cursor-pointer hover:bg-gray-200 transition" @click="open = !open">
        <div class="flex-grow">
            <h4 class="font-bold text-lg text-indigo-700 flex items-center">
                <i class="fas fa-sitemap mr-2"></i>
                {{ $unit->name }} <span class="text-sm font-normal text-gray-500 ml-2">({{ $unit->level }})</span>
            </h4>
        </div>
        <div class="flex items-center space-x-2 flex-shrink-0">
             @can('update', $unit)
                <a href="{{ route('admin.units.edit', $unit) }}" class="text-gray-500 hover:text-indigo-600 p-1 rounded-full text-xs" title="Edit Unit">
                    <i class="fas fa-edit"></i>
                </a>
            @endcan
            @can('delete', $unit)
                <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus unit ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-500 hover:text-red-600 p-1 rounded-full text-xs" title="Hapus Unit">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            @endcan
            <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>
    </div>

    {{-- Konten yang bisa di-collapse --}}
    <div x-show="open" x-transition class="p-4 border-t border-gray-200">
        {{-- Tampilkan daftar pengguna di dalam unit ini --}}
        <h5 class="font-semibold text-gray-600 mb-2 ml-1">Pengguna di Unit Ini:</h5>
        <ul class="pl-6 border-l-2 border-indigo-200 space-y-2">
            @forelse($unit->users as $user)
                <li class="flex items-center text-gray-800">
                    <i class="fas fa-user mr-3 text-gray-400"></i>
                    <div>
                        <span class="font-medium">{{ $user->name }}</span>
                        <span class="text-sm text-gray-600"> - ({{ $user->role }})</span>
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500 italic">-- Tidak ada pengguna di unit ini --</li>
            @endforelse
        </ul>

        {{-- Panggil partial ini secara rekursif untuk setiap sub-unit --}}
        @if ($unit->childrenRecursive->isNotEmpty())
            <h5 class="font-semibold text-gray-600 mt-4 mb-2 ml-1">Sub-Unit:</h5>
            <div class="space-y-4">
                @foreach ($unit->childrenRecursive as $child)
                    @include('users.partials.unit-hierarchy-row', ['unit' => $child, 'level' => $level + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>
