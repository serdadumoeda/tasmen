@php
    // MODIFIKASI: Ambil data dari properti yang sudah kita "suntikkan" di controller
    // Ini membuat view menjadi lebih bersih dan logikanya terpusat di controller.
    $totalAssignedHours = $user->total_assigned_hours ?? 0;
    
    // Kita masih perlu melakukan query ini di sini karena kita ingin menampilkan jumlah tugas spesifik di baris ini.
    // Controller hanya menyediakan total jam, bukan jumlah tugas.
    $activeTasksCount = $user->tasks()->whereIn('status', ['pending', 'in_progress'])->count();

    $weeklyCapacity = 40; // Kapasitas kerja standar per minggu
    $utilization = ($weeklyCapacity > 0) ? round(($totalAssignedHours / $weeklyCapacity) * 100) : 0;
    
    // Menghitung jumlah SK, sama seperti sebelumnya.
    $skCount = $user->special_assignments_count ?? $user->specialAssignments->count();

    // Logika untuk menentukan teks dan warna status beban kerja (tidak berubah)
    $statusText = 'Ideal';
    $statusColor = 'text-green-600';
    $statusIcon = 'fas fa-check-circle'; // Default icon
    if ($utilization > 100 || $skCount >= 3) {
        $statusText = 'Beban Berlebih';
        $statusColor = 'text-red-600';
        $statusIcon = 'fas fa-exclamation-triangle';
    } elseif ($utilization > 85 || $skCount >= 2) {
        $statusText = 'Kapasitas Penuh';
        $statusColor = 'text-amber-600';
        $statusIcon = 'fas fa-hourglass-half';
    }
    
    // Menggunakan relasi 'children' yang asli dari model untuk tombol expand/collapse
    $hasChildren = $user->children && $user->children->isNotEmpty();
@endphp

{{-- 
  Setiap 'keluarga' (induk + anak) dibungkus dalam <tbody>-nya sendiri yang memiliki scope `x-data` dari Alpine.js.
  Ini memastikan setiap tombol expand/collapse bekerja secara independen.
--}}
<tbody x-data="{ open: true }" class="border-t border-gray-200">
    <tr class="bg-white hover:bg-gray-50 transition-colors duration-150"> {{-- Menambahkan transisi hover --}}
        {{-- Kolom Nama Personil & Role --}}
        <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ $level * 1.5 + 1.5 }}rem;">
            <div class="flex items-center">
                @if($hasChildren)
                    <button @click="open = !open" class="mr-2 text-gray-500 hover:text-gray-900 focus:outline-none p-1 rounded-md hover:bg-gray-100 transition-colors duration-150"> {{-- Tombol expand/collapse lebih modern --}}
                        <svg class="h-4 w-4 transform transition-transform" :class="{ 'rotate-90': !open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                @else
                    <div class="w-4 h-4 mr-2"></div> {{-- Placeholder agar sejajar --}}
                @endif
                <div class="flex items-center">
                    <i class="fas fa-user-circle mr-2 text-gray-500"></i> {{-- Icon user --}}
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $user->role }}</div>
                    </div>
                </div>
            </div>
        </td>

        {{-- MODIFIKASI: Kolom Tugas Proyek sekarang menampilkan jumlah tugas aktif (Proyek + Ad-Hoc) --}}
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800 shadow-sm"> {{-- Badge modern --}}
                <i class="fas fa-list-check mr-1"></i> {{ $activeTasksCount }}
            </span>
        </td>
        
        {{-- Kolom Utilisasi Proyek (Jam) --}}
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner"> {{-- Tinggi progress bar lebih besar, shadow-inner --}}
                <div class="{{ $utilization > 100 ? 'bg-red-500' : ($utilization > 85 ? 'bg-yellow-500' : 'bg-green-500') }} h-4 rounded-full shadow-md" style="width: {{ min($utilization, 100) }}%"></div> {{-- Shadow pada progress bar --}}
            </div>
            {{-- MODIFIKASI: Menggunakan variabel baru dari controller --}}
            <div class="text-xs text-gray-600 mt-1 flex items-center justify-center">
                <i class="fas fa-chart-line mr-1 {{ $utilization > 100 ? 'text-red-500' : ($utilization > 85 ? 'text-yellow-500' : 'text-green-500') }}"></i> {{-- Icon chart --}}
                {{ $totalAssignedHours }} jam (<span class="font-bold {{ $utilization > 100 ? 'text-red-700' : ($utilization > 85 ? 'text-amber-700' : 'text-green-700') }}">{{ $utilization }}%</span>)
            </div>
        </td>
        
        {{-- Kolom Beban SK Aktif --}}
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <a href="{{ route('special-assignments.index', ['personnel_id' => $user->id]) }}" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 hover:bg-purple-200 shadow-sm transition-colors duration-150"> {{-- Badge modern --}}
                <i class="fas fa-file-signature mr-1"></i> {{ $skCount }} SK
            </a>
        </td>

        {{-- Kolom Status Beban --}}
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold {{ $statusColor }}"> {{-- Font bold --}}
            <i class="{{ $statusIcon }} mr-1"></i> {{-- Menambahkan ikon status --}}
            {{ $statusText }}
        </td>
    </tr>

    {{-- Logika rekursif untuk menampilkan bawahan (tidak berubah) --}}
    @if ($hasChildren)
        <tr x-show="open" x-transition.opacity>
            <td colspan="5" class="p-0 border-0">
                 @foreach ($user->children as $child)
                    <table class="w-full">
                        @include('workload-analysis._workload-row', ['user' => $child, 'level' => $level + 1])
                    </table>
                @endforeach
            </td>
        </tr>
    @endif
</tbody>