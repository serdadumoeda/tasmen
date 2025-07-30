<div style="padding-left: {{ $level * 20 }}px;" class="py-2 border-b border-gray-200">
    <div class="flex items-center justify-between">
        <div>
            <div class="font-semibold">{{ $user->name }}</div>
            <div class="text-sm text-gray-500">{{ $user->email }} - {{ $user->role }}</div>
        </div>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
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
