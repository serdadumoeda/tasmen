@php $level = $level ?? 0; @endphp

<tr class="hover:bg-gray-50 transition-colors duration-150">
    <td class="py-2 px-4 border-b text-sm text-gray-900 font-medium">
        <span style="padding-left: {{ $level * 20 }}px;">
            @if(count($unit->allChildren) > 0)
                <i class="fas fa-folder-open text-yellow-500 mr-2"></i>
            @else
                <i class="fas fa-folder text-gray-400 mr-2"></i>
            @endif
            {{ $unit->name }}
        </span>
    </td>
    <td class="py-2 px-4 border-b text-sm text-gray-700">{{ $unit->kepalaUnit->name ?? '---' }}</td>
    <td class="py-2 px-4 border-b text-sm text-gray-700">{{ $unit->parentUnit->name ?? '-' }}</td>
    <td class="py-2 px-4 border-b text-center">
        <a href="{{ route('admin.units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center p-2 rounded-full hover:bg-indigo-50 transition-colors duration-200" title="Edit Unit">
            <i class="fas fa-edit"></i>
        </a>
        <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded-full hover:bg-red-50 transition-colors duration-200" title="Hapus Unit">
                <i class="fas fa-trash-can"></i>
            </button>
        </form>
    </td>
</tr>

@if (count($unit->allChildren) > 0)
    @foreach ($unit->allChildren as $child)
        @include('admin.units.partials.unit-row', ['unit' => $child, 'level' => $level + 1])
    @endforeach
@endif