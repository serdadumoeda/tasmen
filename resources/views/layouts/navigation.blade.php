@php
// Helper untuk membuat inisial dari nama pengguna
$userName = Auth::user()->name;
$words = explode(' ', $userName);
$initials = '';
if (count($words) >= 2) {
    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
} else {
    $initials = strtoupper(substr($words[0], 0, 2));
}
@endphp

<nav x-data="{ open: false }" class="bg-[#00796B] border-b border-green-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <div class="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex">
                    {{-- =================== MENU UTAMA =================== --}}
                    @if (Auth::user()->isTopLevelManager())
                        <x-nav-link :href="route('executive.summary')" :active="request()->routeIs('executive.summary')">
                            Executive Summary
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-nav-link>
                    @endif

                    {{-- =================== MENU KERJA =================== --}}
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center h-full px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs(['adhoc-tasks.*', 'projects.*', 'special-assignments.*']) ? 'border-yellow-300 text-white' : 'border-transparent text-white hover:text-gray-200 hover:border-yellow-300/75' }}">
                                    <div>Menu Kerja</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">Tugas Harian</x-dropdown-link>
                                <x-dropdown-link :href="route('special-assignments.index')" :active="request()->routeIs('special-assignments.*')">SK Penugasan</x-dropdown-link>
                                <div class="border-t border-gray-200"></div>
                                <div class="block px-4 py-2 text-xs text-gray-400">Akses Cepat Proyek</div>
                                @forelse ($quickProjects as $project)
                                    <x-dropdown-link :href="route('projects.show', $project)">{{ Str::limit($project->name, 30) }}</x-dropdown-link>
                                @empty
                                    <div class="px-4 py-2 text-sm text-gray-500">Belum ada proyek.</div>
                                @endforelse
                                @can('create', App\Models\Project::class)
                                <div class="border-t border-gray-200"></div>
                                <x-dropdown-link :href="route('projects.create.step1')" class="font-semibold text-blue-600"><i class="fa-solid fa-plus-circle mr-2"></i>Buat Proyek Baru</x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- =================== DROPDOWN MANAJEMEN TIM (Untuk Pimpinan) =================== --}}
                    @if(Auth::user()->canManageUsers())
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center h-full px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs(['users.*', 'workload.*', 'resource-pool.*', 'peminjaman-requests.*']) ? 'border-yellow-300 text-white' : 'border-transparent text-white hover:text-gray-200 hover:border-yellow-300/75' }}">
                                    <div>Manajemen Tim</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @if(Auth::user()->role == 'Superadmin')
                                    <x-dropdown-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">Manajemen Unit</x-dropdown-link>
                                @endif
                                @can('viewAny', App\Models\User::class)
                                    <x-dropdown-link :href="route('users.index')" :active="request()->routeIs('users.*')">Manajemen Pengguna</x-dropdown-link>
                                @endcan
                                @if(Auth::user()->isTopLevelManager())
                                    <x-dropdown-link :href="route('workload.analysis')" :active="request()->routeIs('workload.analysis')">Analisis Beban Kerja</x-dropdown-link>
                                @endif
                                <x-dropdown-link :href="route('weekly-workload.index')" :active="request()->routeIs('weekly-workload.index')">Beban Kerja Mingguan</x-dropdown-link>
                                <div class="border-t border-gray-200"></div>
                                <x-dropdown-link :href="route('peminjaman-requests.my-requests')" :active="request()->routeIs('peminjaman-requests.*')">Peminjaman Anggota</x-dropdown-link>
                                <x-dropdown-link :href="route('resource-pool.index')" :active="request()->routeIs('resource-pool.index')">Resource Pool</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-notification-dropdown />

                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center justify-center w-10 h-10 bg-green-700/50 rounded-full text-white text-sm font-bold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-800 focus:ring-white transition ease-in-out duration-150">
                                {{ $initials }}
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')"><i class="fa-solid fa-user-gear w-4 mr-2"></i>Profil</x-dropdown-link>
                            <div class="border-t border-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600"><i class="fa-solid fa-right-from-bracket w-4 mr-2"></i>Log Out</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-green-800 focus:outline-none focus:bg-green-800 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
             @if(Auth::user()->isTopLevelManager())
                <x-responsive-nav-link :href="route('executive.summary')" :active="request()->routeIs('executive.summary')">Executive Summary</x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">Tugas Harian</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('special-assignments.index')" :active="request()->routeIs('special-assignments.*')">SK Penugasan</x-responsive-nav-link>
            {{-- Tambahkan menu lain untuk mobile jika diperlukan --}}
        </div>
        <div class="pt-4 pb-1 border-t border-green-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-300">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>