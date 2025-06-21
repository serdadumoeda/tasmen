@php
    $totalAssignedHours = $user->tasks->sum('estimated_hours');
    $weeklyCapacity = 40;
    $utilization = ($weeklyCapacity > 0) ? round(($totalAssignedHours / $weeklyCapacity) * 100) : 0;
    $skCount = $user->special_assignments_count ?? $user->specialAssignments->count();

    $statusText = 'Ideal';
    $statusColor = 'text-green-600';
    if ($utilization > 100 || $skCount >= 3) {
        $statusText = 'Beban Berlebih';
        $statusColor = 'text-red-600';
    } elseif ($utilization > 85 || $skCount >= 2) {
        $statusText = 'Kapasitas Penuh';
        $statusColor = 'text-amber-600';
    }
    
    // Gunakan relasi 'children' yang asli dari model
    $hasChildren = $user->children && $user->children->isNotEmpty();
@endphp

{{-- 
  PERBAIKAN STRUKTUR FINAL:
  Setiap 'keluarga' (induk + anak) dibungkus dalam <tbody>-nya sendiri yang memiliki scope `x-data`.
  Ini adalah cara yang paling andal untuk membuat komponen interaktif di dalam tabel.
--}}
<tbody x-data="{ open: true }" class="border-t border-gray-200">
    <tr class="bg-white hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ $level * 1.5 + 1.5 }}rem;">
            <div class="flex items-center">
                @if($hasChildren)
                    <button @click="open = !open" class="mr-2 text-gray-500 hover:text-gray-900 focus:outline-none">
                        <svg class="h-4 w-4 transform transition-transform" :class="{ 'rotate-90': !open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                @else
                    <div class="w-4 h-4 mr-2"></div> {{-- Placeholder agar sejajar --}}
                @endif
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500">{{ $user->role }}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">{{ $user->tasks->count() }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="{{ $utilization > 100 ? 'bg-red-500' : ($utilization > 85 ? 'bg-yellow-500' : 'bg-green-500') }} h-2.5 rounded-full" style="width: {{ min($utilization, 100) }}%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ $totalAssignedHours }} jam ({{ $utilization }}%)</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <a href="{{ route('special-assignments.index', ['personnel_id' => $user->id]) }}" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 hover:bg-purple-200">
                {{ $skCount }} SK
            </a>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold {{ $statusColor }}">
            {{ $statusText }}
        </td>
    </tr>

    @if ($hasChildren)
        {{-- Baris ini hanya akan muncul jika `open` bernilai true --}}
        <tr x-show="open" x-transition.opacity>
            <td colspan="5" class="p-0 border-0">
                 @foreach ($user->children as $child)
                    <table class="w-full">
                         {{-- Panggil partial ini lagi secara rekursif --}}
                        @include('workload-analysis._workload-row', ['user' => $child, 'level' => $level + 1])
                    </table>
                @endforeach
            </td>
        </tr>
    @endif
</tbody>