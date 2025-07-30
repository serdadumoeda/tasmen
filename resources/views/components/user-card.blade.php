@props(['user'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img class="h-12 w-12 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" alt="">
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">
                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">{{ $user->name }}</a>
                </div>
                <div class="text-sm text-gray-500">
                    {{ $user->email }}
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-sm text-gray-500">
                <span class="font-semibold">Peran:</span> {{ $user->role }}
            </div>
            <div class="text-sm text-gray-500">
                <span class="font-semibold">Unit:</span> {{ $user->unit->name ?? '-' }}
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <a href="{{ route('users.edit', $user) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Edit</a>
        </div>
    </div>
</div>
