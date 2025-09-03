@props(['item', 'level' => 0])

<li class="pl-{{ $level * 4 }} border-l-2 {{ $level > 0 ? 'border-gray-200 ml-4' : 'border-transparent' }}">
    <div class="p-3 rounded-md hover:bg-gray-50">
        <div class="flex items-center">
            <i class="fas fa-user-circle text-gray-400 mr-3 text-xl"></i>
            <div>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $item->penerima->name }}
                    <span class="text-xs font-normal text-gray-500">
                        (dari {{ $item->pengirim->name }})
                    </span>
                </p>
                <p class="text-xs text-gray-600 italic">"{{ $item->instruksi }}"</p>
                <span class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
            </div>
        </div>

        @if ($item->children->isNotEmpty())
            <ul class="mt-3 space-y-2">
                @foreach ($item->children as $child)
                    <x-disposisi-item :item="$child" :level="$level + 1" />
                @endforeach
            </ul>
        @endif
    </div>
</li>
