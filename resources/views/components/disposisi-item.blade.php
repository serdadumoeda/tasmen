@props(['item', 'level' => 0])

{{-- The 'ml' class is calculated based on the recursion level for indentation --}}
<li class="ml-{{ $level * 6 }} list-none">
    <div class="relative pl-8">
        {{-- Vertical line connecting tree nodes --}}
        <div class="absolute left-0 top-0 h-full border-l-2 border-gray-300"></div>
        {{-- Horizontal line connecting to the item --}}
        <div class="absolute left-0 top-4 w-6 border-t-2 border-gray-300"></div>
        {{-- Dot for the item itself --}}
        <div class="absolute left-[-0.3rem] top-3 h-3 w-3 rounded-full bg-indigo-600 border-2 border-white"></div>

        <div class="p-3 rounded-md hover:bg-gray-50">
            <div class="flex items-center">
                <i class="fas fa-user-circle text-gray-400 mr-3 text-xl"></i>
                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $item->penerima->name ?? 'N/A' }}
                        <span class="text-xs font-normal text-gray-500">
                            (dari {{ $item->pengirim->name ?? 'N/A' }})
                        </span>
                    </p>
                    <p class="text-xs text-gray-600 italic">"{{ $item->instruksi }}"</p>
                    <span class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                </div>
            </div>

            @if ($item->tembusanUsers && $item->tembusanUsers->isNotEmpty())
                <div class="mt-2 ml-9">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-copy mr-1"></i>
                        Tembusan:
                        @foreach($item->tembusanUsers as $user)
                            <span class="font-medium">{{ $user->name }}</span>{{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Recursive call for children --}}
    @if ($item->childrenRecursive && $item->childrenRecursive->isNotEmpty())
        <ul class="mt-3 space-y-2">
            @foreach ($item->childrenRecursive as $child)
                <x-disposisi-item :item="$child" :level="$level + 1" />
            @endforeach
        </ul>
    @endif
</li>
