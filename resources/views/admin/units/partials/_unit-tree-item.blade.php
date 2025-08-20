@php $level = $level ?? 0; @endphp

<div x-data="{ open: {{ $level < 1 ? 'true' : 'false' }} }" class="my-1">
    {{-- Baris untuk unit saat ini --}}
    <div class="flex items-center bg-white p-3 rounded-lg shadow-sm hover:bg-gray-50 transition-colors duration-150">
        {{-- Indentasi dan Tombol Buka/Tutup --}}
        <div class="w-1/3 flex items-center" style="padding-left: {{ $level * 25 }}px;">
            @if($unit->childrenRecursive->isNotEmpty())
                <button @click="open = !open" class="mr-2 text-gray-500 hover:text-gray-800 focus:outline-none w-6 text-center">
                    <i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                </button>
            @else
                <span class="w-6 mr-2"></span> {{-- Spacer for alignment --}}
            @endif
            <i class="fas fa-folder text-yellow-500 mr-2"></i>
            <span class="font-semibold text-gray-800">{{ $unit->name }}</span>
        </div>

        {{-- Kepala Unit --}}
        <div class="w-1/4 text-sm text-gray-600">
            {{ $unit->kepalaUnit->name ?? '---' }}
        </div>

        {{-- Unit Atasan --}}
        <div class="w-1/4 text-sm text-gray-600">
            {{ $unit->parentUnit->name ?? '-' }}
        </div>

        {{-- Aksi --}}
        <div class="w-1/6 text-right">
            <a href="{{ route('admin.units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-900 px-2 py-1 rounded-md hover:bg-indigo-50 transition-colors duration-200" title="Edit Unit">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Yakin ingin menghapus unit ini beserta seluruh turunannya?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900 px-2 py-1 rounded-md hover:bg-red-50 transition-colors duration-200" title="Hapus Unit">
                    <i class="fas fa-trash-can"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Kontainer untuk anak-anak unit (rekursif) --}}
    @if ($unit->childrenRecursive->isNotEmpty())
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="ml-6 mt-1 border-l-2 border-gray-200 pl-4">
            @foreach ($unit->childrenRecursive as $child)
                @include('admin.units.partials._unit-tree-item', ['unit' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
