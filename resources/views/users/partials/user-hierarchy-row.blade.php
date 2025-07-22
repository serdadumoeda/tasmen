<div class="p-4 border-b border-gray-200" style="margin-left: {{ ($user->unit ? $user->unit->getLevelNumber() : 0) * 10 }}px;">
    <div class="flex justify-between items-center">
        <div>
            <p class="font-bold text-gray-900">{{ $user->name }}</p>
            <p class="text-sm text-gray-600">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ $user->role }}
                </span>
                <span class="text-gray-400 mx-1">|</span>
                Unit: {{ $user->unit->name ?? '-' }}
            </p>
        </div>
        <div class="flex items-center space-x-2">
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
            @endcan
            @can('delete', $user)
                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Hapus</button>
                </form>
            @endcan
        </div>
    </div>
</div>
