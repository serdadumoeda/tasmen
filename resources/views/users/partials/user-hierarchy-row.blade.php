<div x-data="{ open: true }" class="py-4 px-6 border-b border-gray-100 bg-white hover:bg-gray-50 transition-colors duration-150" style="padding-left: {{ $level * 20 }}px;">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            {{-- Tombol expand/collapse untuk hirarki --}}
            @if(optional(optional($user->unit)->children)->count() > 0)
                <button @click="open = !open" class="mr-3 text-gray-500 hover:text-gray-900 focus:outline-none p-1 rounded-md hover:bg-gray-100 transition-colors duration-150">
                    <i class="fas fa-chevron-right transform transition-transform" :class="{ 'rotate-90': open }"></i>
                </button>
            @else
                <div class="w-6 h-6 mr-3 flex-shrink-0"></div>
            @endif
            
            <div class="flex items-center">
                <i class="fas fa-user-circle mr-3 text-gray-500 fa-lg"></i>
                <div>
                    <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-600">{{ $user->role }}</div>
                    <div class="text-xs text-gray-500 flex items-center mt-1">
                        <i class="fas fa-envelope mr-1"></i> {{ $user->email }}
                    </div>
                    @if($user->unit)
                        <div class="text-xs text-gray-500 flex items-center mt-0.5">
                            <i class="fas fa-building mr-1"></i> {{ $user->unit->name }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2 text-sm font-medium">
            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 p-2 rounded-full hover:bg-indigo-50 transition-colors duration-200" title="Edit Pengguna">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded-full hover:bg-red-50 transition-colors duration-200" title="Hapus Pengguna">
                    <i class="fas fa-trash-can"></i>
                </button>
            </form>
        </div>
    </div>
    
    {{-- Logika rekursif untuk menampilkan bawahan --}}
    @if(optional(optional($user->unit)->children)->count() > 0)
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="mt-2 space-y-0.5">
            @foreach($user->unit->children as $childUnit)
                @foreach($childUnit->users as $childUser)
                     @include('users.partials.user-hierarchy-row', ['user' => $childUser, 'level' => $level + 1])
                @endforeach
            @endforeach
        </div>
    @endif
</div>