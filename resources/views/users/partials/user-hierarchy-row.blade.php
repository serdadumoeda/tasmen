<div style="padding-left: {{ $level * 20 }}px;" class="py-2 border-b border-gray-200">
    <div class="flex items-center justify-between">
        <div>
            <div class="font-semibold">{{ $user->name }}</div>
            <div class="text-sm text-gray-500">{{ $user->unit->name ?? '' }}</div>
            <div class="text-sm text-gray-500">{{ $user->email }}</div>
        </div>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block ml-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">Hapus</button>
            </form>
        </div>
    </div>
    @if($user->unit && $user->unit->children->count() > 0)
        <div class="mt-2 space-y-2">
            @foreach($user->unit->children as $childUnit)
                @foreach($childUnit->users as $childUser)
                    @include('users.partials.user-hierarchy-row', ['user' => $childUser, 'level' => $level + 1])
                @endforeach
            @endforeach
        </div>
    @endif
</div>
