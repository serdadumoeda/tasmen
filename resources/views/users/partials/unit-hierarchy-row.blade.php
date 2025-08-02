@php $level = $level ?? 0; @endphp

{{-- Tampilkan baris untuk Unit --}}
<div class="p-4 rounded-lg bg-gray-100 border border-gray-200" style="margin-left: {{ $level * 20 }}px;">
    <h4 class="font-bold text-lg text-indigo-700 flex items-center">
        <i class="fas fa-sitemap mr-2"></i>
        {{ $unit->name }} <span class="text-sm font-normal text-gray-500 ml-2">({{ $unit->level }})</span>
    </h4>

    {{-- Tampilkan daftar pengguna di dalam unit ini --}}
    <ul class="mt-2 pl-6 border-l-2 border-indigo-200 space-y-2">
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
</div>

{{-- Panggil partial ini secara rekursif untuk setiap sub-unit --}}
@if ($unit->childrenRecursive->isNotEmpty())
    <div class="mt-4 space-y-4">
        @foreach ($unit->childrenRecursive as $child)
            @include('users.partials.unit-hierarchy-row', ['unit' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
