<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Unit') }}
            </h2>
            <div>
                <a href="{{ route('admin.units.workflow') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-project-diagram mr-2"></i> {{ __('Lihat Alur Kerja') }}
                </a>
                <a href="{{ route('admin.units.create') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i> {{ __('Tambah Unit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">

            <!-- Search Input -->
            <div class="mb-6 bg-white p-4 rounded-xl shadow-lg border border-gray-200">
                <div class="flex items-center">
                    <i class="fas fa-search text-gray-400 mr-3"></i>
                    <input type="text" id="hierarchy-search" placeholder="Cari nama pengguna atau unit..." class="w-full border-0 focus:ring-0 text-sm">
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                    <h1 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-network-wired mr-2 text-indigo-600"></i> Struktur Organisasi Unit
                    </h1>

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative shadow-md" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @forelse ($units as $unit)
                        @include('users.partials.unit-hierarchy-row', ['unit' => $unit, 'level' => 0, 'users' => $users])
                    @empty
                        <div class="px-6 py-8 text-center text-lg text-gray-500">
                            <i class="fas fa-building-circle-exclamation fa-3x text-gray-400 mb-4"></i>
                            <p>Tidak ada struktur unit yang dapat ditampilkan.</p>
                            @if(Auth::user()->isSuperAdmin())
                                <p class="text-sm text-gray-400 mt-2">
                                    Silakan tambahkan unit kerja baru melalui <a href="{{ route('admin.units.create') }}" class="text-indigo-600 hover:underline">halaman ini</a>.
                                </p>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('hierarchy-search');
            const hierarchyContainer = document.querySelector('.p-6.bg-white');
            const allUnits = Array.from(hierarchyContainer.querySelectorAll('.bg-gray-100.rounded-lg.border'));

            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase().trim();

                if (searchTerm === '') {
                    allUnits.forEach(unitDiv => {
                        unitDiv.style.display = '';
                        const xData = unitDiv.__x.$data;
                        if (xData && typeof xData.open !== 'undefined') {
                            xData.open = true;
                        }
                    });
                    return;
                }

                allUnits.slice().reverse().forEach(unitDiv => {
                    let hasMatch = false;

                    const unitTitle = unitDiv.querySelector('h4');
                    if (unitTitle && unitTitle.textContent.toLowerCase().includes(searchTerm)) {
                        hasMatch = true;
                    }

                    const usersInUnit = unitDiv.querySelectorAll('ul > li');
                    usersInUnit.forEach(userLi => {
                        if (userLi.textContent.toLowerCase().includes(searchTerm)) {
                            hasMatch = true;
                            userLi.style.display = '';
                        } else {
                            userLi.style.display = 'none';
                        }
                    });

                    const childUnits = unitDiv.querySelectorAll(':scope > .p-4.border-t > .space-y-4 > .bg-gray-100');
                    childUnits.forEach(childDiv => {
                        if (childDiv.style.display !== 'none') {
                            hasMatch = true;
                        }
                    });

                    if (hasMatch) {
                        unitDiv.style.display = '';
                        const xData = unitDiv.__x.$data;
                        if (xData && typeof xData.open !== 'undefined') {
                            xData.open = true;
                        }
                    } else {
                        unitDiv.style.display = 'none';
                    }
                });
            });
        });
    </script>
</x-app-layout>