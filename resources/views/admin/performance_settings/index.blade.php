@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Pengaturan Performa</h1>
    @if(session('success'))
      <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
        {{ session('success') }}
      </div>
    @endif

    <form action="{{ route('admin.performance_settings.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Bobot peran manajerial --}}
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">Bobot Jabatan Manajerial</h2>
            <p class="text-sm text-gray-600 mb-4">Nilai antara 0 dan 1 yang menentukan pengaruh skor bawahan.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($settings['manager_weights'] ?? [] as $role => $weight)
                    <div>
                        <label class="block font-medium" for="manager_weights[{{ $role }}]">
                            {{ ucwords(str_replace('_', ' ', strtolower($role))) }}
                        </label>
                        <input type="number" step="0.01" min="0" max="1"
                               name="manager_weights[{{ $role }}]"
                               id="manager_weights[{{ $role }}]"
                               value="{{ old('manager_weights.'.$role, $weight) }}"
                               class="mt-1 block w-full border-gray-300 rounded" />
                        @error('manager_weights.' . $role)
                          <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Batas efisiensi --}}
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">Batas Faktor Efisiensi</h2>
            <p class="text-sm text-gray-600 mb-4">Rasio estimasi terhadap aktual akan dikapitasi di antara nilai ini.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium" for="efficiency_cap[min]">Minimum</label>
                    <input type="number" step="0.01" min="0"
                           name="efficiency_cap[min]" id="efficiency_cap[min]"
                           value="{{ old('efficiency_cap.min', $settings['efficiency_cap']['min'] ?? '') }}"
                           class="mt-1 block w-full border-gray-300 rounded" />
                    @error('efficiency_cap.min')
                      <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-medium" for="efficiency_cap[max]">Maksimum</label>
                    <input type="number" step="0.01" min="0"
                           name="efficiency_cap[max]" id="efficiency_cap[max]"
                           value="{{ old('efficiency_cap.max', $settings['efficiency_cap']['max'] ?? '') }}"
                           class="mt-1 block w-full border-gray-300 rounded" />
                    @error('efficiency_cap.max')
                      <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Ambang predikat kinerja --}}
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">Ambang Predikat Kinerja</h2>
            <p class="text-sm text-gray-600 mb-4">Nilai minimum untuk tiap predikat.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($settings['rating_thresholds'] ?? [] as $label => $threshold)
                    <div>
                        <label class="block font-medium"
                               for="rating_thresholds[{{ $label }}]">
                          {{ ucwords(str_replace('_', ' ', $label)) }}
                        </label>
                        <input type="number" step="0.01" min="0"
                               name="rating_thresholds[{{ $label }}]"
                               id="rating_thresholds[{{ $label }}]"
                               value="{{ old('rating_thresholds.'.$label, $threshold) }}"
                               class="mt-1 block w-full border-gray-300 rounded" />
                        @error('rating_thresholds.' . $label)
                          <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ambang beban kerja mingguan --}}
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">Ambang Beban Kerja Mingguan</h2>
            <p class="text-sm text-gray-600 mb-4">
              Persentase jam kerja dibanding standar jam kerja. Zona hijau &lt; ambang hijau,
              kuning di antara hijau dan kuning, merah &gt; ambang kuning.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium"
                           for="weekly_workload_thresholds[green]">Ambang Hijau (%)</label>
                    <input type="number" step="0.1" min="0"
                           name="weekly_workload_thresholds[green]"
                           id="weekly_workload_thresholds[green]"
                           value="{{ old('weekly_workload_thresholds.green', $settings['weekly_workload_thresholds']['green'] ?? '') }}"
                           class="mt-1 block w-full border-gray-300 rounded" />
                    @error('weekly_workload_thresholds.green')
                      <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-medium"
                           for="weekly_workload_thresholds[yellow]">Ambang Kuning (%)</label>
                    <input type="number" step="0.1" min="0"
                           name="weekly_workload_thresholds[yellow]"
                           id="weekly_workload_thresholds[yellow]"
                           value="{{ old('weekly_workload_thresholds.yellow', $settings['weekly_workload_thresholds']['yellow'] ?? '') }}"
                           class="mt-1 block w-full border-gray-300 rounded" />
                    @error('weekly_workload_thresholds.yellow')
                      <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Pengaturan ABK --}}
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">Pengaturan Analisis Beban Kerja (ABK)</h2>
            <p class="text-sm text-gray-600 mb-4">Parameter yang digunakan dalam perhitungan modul ABK.</p>
            <div>
                <label class="block font-medium" for="abk_effective_hours_per_year">Jam Kerja Efektif per Tahun</label>
                <input type="number" step="1" min="1"
                       name="abk_effective_hours_per_year" id="abk_effective_hours_per_year"
                       value="{{ old('abk_effective_hours_per_year', $settings['abk_effective_hours_per_year'] ?? 1500) }}"
                       class="mt-1 block w-full border-gray-300 rounded" />
                @error('abk_effective_hours_per_year')
                  <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
              Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
