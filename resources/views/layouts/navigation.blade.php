@php
// Helper untuk membuat inisial dari nama pengguna
$userName = Auth::user()->name;
$words = explode(' ', $userName);
$initials = '';
if (count($words) >= 2) {
    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count(array_keys($words)) - 1], 0, 1));
} else {
    $initials = strtoupper(substr($words[0], 0, 2));
}
@endphp

{{-- Menyatukan state Alpine.js di sini untuk mengelola semua dropdown dan modal --}}
<nav x-data="{ open: false, showAboutModal: false }" class="bg-[#00796B] border-b border-green-800 shadow-2xl relative z-50"> 
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto text-white fill-current transform hover:scale-105 transition-transform duration-200" />
                    </a>
                </div>

                <div class="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard', 'executive.summary')" class="text-white hover:text-yellow-300 transition duration-150 ease-in-out">
                        <div class="flex items-center">
                            <i class="fas fa-home mr-2"></i>
                            <span>Beranda</span>
                        </div>
                    </x-nav-link>

                    {{-- Dropdown Menu Kerja --}}
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center h-full px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                    {{ (request()->routeIs(['global.dashboard', 'adhoc-tasks.*', 'special-assignments.*', 'projects.show', 'projects.create.*'])) && !request()->routeIs('executive.summary')
                                        ? 'border-yellow-300 text-white bg-green-700/50'
                                        : 'border-transparent text-white hover:text-yellow-300 hover:border-yellow-300/75 focus:outline-none focus:text-white focus:border-yellow-300/75' }}">
                                    <div><i class="fas fa-briefcase mr-2"></i>Menu Kerja</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10">
                                    <x-dropdown-link :href="route('global.dashboard')" :active="request()->routeIs('global.dashboard')">Daftar Kegiatan</x-dropdown-link>
                                    <div class="border-t border-gray-200"></div>
                                    <x-dropdown-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">Tugas Harian</x-dropdown-link>
                                    <x-dropdown-link :href="route('special-assignments.index')" :active="request()->routeIs('special-assignments.*')">SK Penugasan</x-dropdown-link>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- Menu Cuti --}}
                    <x-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.*')" class="text-white hover:text-yellow-300 transition duration-150 ease-in-out">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>Manajemen Cuti</span>
                        </div>
                    </x-nav-link>

                    {{-- Dropdown Laporan & Analisis --}}
                    @if (Auth::user()->isTopLevelManager())
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="60">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center h-full px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                        {{ request()->routeIs(['workload.analysis', 'weekly-workload.index'])
                                            ? 'border-yellow-300 text-white bg-green-700/50'
                                            : 'border-transparent text-white hover:text-yellow-300 hover:border-yellow-300/75 focus:outline-none focus:text-white focus:border-yellow-300/75' }}">
                                        <div><i class="fas fa-chart-pie mr-2"></i>Laporan & Analisis</div>
                                        <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10">
                                        <x-dropdown-link :href="route('workload.analysis')" :active="request()->routeIs('workload.analysis')">Analisis Beban Kerja</x-dropdown-link>
                                        <x-dropdown-link :href="route('weekly-workload.index')" :active="request()->routeIs('weekly-workload.index')">Beban Kerja Mingguan</x-dropdown-link>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    {{-- Dropdown Manajemen Tim --}}
                    @if(Auth::user()->canManageUsers())
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="60">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center h-full px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                        {{ request()->routeIs(['users.*', 'admin.units.*', 'resource-pool.*', 'peminjaman-requests.*']) && !request()->routeIs('admin.api_keys.*', 'admin.activities.index')
                                            ? 'border-yellow-300 text-white bg-green-700/50'
                                            : 'border-transparent text-white hover:text-yellow-300 hover:border-yellow-300/75 focus:outline-none focus:text-white focus:border-yellow-300/75' }}">
                                        <div><i class="fas fa-users-cog mr-2"></i>Manajemen Tim</div>
                                        <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10">
                                        @if(Auth::user()->isSuperAdmin())
                                            <x-dropdown-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">Manajemen Unit</x-dropdown-link>
                                        @endif
                                        <x-dropdown-link :href="route('users.index')" :active="request()->routeIs('users.*')">Manajemen Pengguna</x-dropdown-link>
                                        <div class="border-t border-gray-200"></div>
                                        <x-dropdown-link :href="route('peminjaman-requests.my-requests')" :active="request()->routeIs('peminjaman-requests.*')">Peminjaman Anggota</x-dropdown-link>
                                        <x-dropdown-link :href="route('resource-pool.index')" :active="request()->routeIs('resource-pool.index')">Resource Pool</x-dropdown-link>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    {{-- Dropdown Pengaturan Sistem (Hanya untuk Superadmin) --}}
                    @if(Auth::user()->isSuperAdmin())
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="60">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center h-full px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out
                                        {{ request()->routeIs(['admin.api_keys.*', 'admin.activities.index'])
                                            ? 'border-yellow-300 text-white bg-green-700/50'
                                            : 'border-transparent text-white hover:text-yellow-300 hover:border-yellow-300/75 focus:outline-none focus:text-white focus:border-yellow-300/75' }}">
                                        <div><i class="fas fa-cogs mr-2"></i>Pengaturan Sistem</div>
                                        <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10">
                                        @if(Auth::user()->canManageLeaveSettings())
                                            <x-dropdown-link :href="route('admin.approval-workflows.index')" :active="request()->routeIs('admin.approval-workflows.*')">Manajemen Alur Persetujuan</x-dropdown-link>
                                            <x-dropdown-link :href="route('admin.cuti-bersama.index')" :active="request()->routeIs('admin.cuti-bersama.*')">Manajemen Cuti Bersama</x-dropdown-link>
                                            <div class="border-t border-gray-200"></div>
                                        @endif
                                        <x-dropdown-link :href="route('admin.api_keys.index')" :active="request()->routeIs('admin.api_keys.index')">Manajemen Integrasi</x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.api_keys.query_helper')" :active="request()->routeIs('admin.api_keys.query_helper')">API Query Helper</x-dropdown-link>
                                        <div class="border-t border-gray-200"></div>
                                        <x-dropdown-link :href="route('admin.activities.index')" :active="request()->routeIs('admin.activities.index')">Log Aktivitas</x-dropdown-link>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- Dropdown Notifikasi --}}
                <div class="ms-3 relative" x-data="notifications()" x-init="fetchUnread()">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button @click="isOpen = !isOpen; markAsRead(null)" class="inline-flex items-center p-2 text-white bg-green-700/50 rounded-full hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-800 focus:ring-white transition ease-in-out duration-150 relative transform hover:scale-110 shadow-md hover:shadow-lg">
                                <i class="fas fa-bell fa-lg"></i>
                                <span x-show="count > 0" x-text="count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center animate-pulse"></span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10">
                                <div class="block px-4 py-2 text-xs text-gray-400">Notifikasi</div>
                                <template x-for="notification in unread" :key="notification.id">
                                    <x-dropdown-link x-bind:href="notification.link" @click="markAsRead(notification.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-100">
                                        <div class="font-semibold" x-text="notification.data.title"></div>
                                        <div class="text-xs text-gray-500" x-text="notification.data.message"></div>
                                        <div class="text-xs text-gray-400 mt-1" x-text="new Date(notification.created_at).toLocaleString()"></div>
                                    </x-dropdown-link>
                                </template>
                                <div x-show="count === 0" class="px-4 py-2 text-sm text-gray-500 text-center">Tidak ada notifikasi baru.</div>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- Tombol pemicu Modal About Us --}}
                <div class="ms-3 relative">
                    <button @click="showAboutModal = true" class="inline-flex items-center p-2 text-white bg-green-700/50 rounded-full hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-800 focus:ring-white transition ease-in-out duration-150 relative transform hover:scale-110 shadow-md hover:shadow-lg">
                        <i class="fas fa-info-circle fa-lg"></i>
                    </button>
                </div>

                {{-- Dropdown Profil Pengguna --}}
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center justify-center w-10 h-10 bg-green-700/75 rounded-full text-white text-sm font-bold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-800 focus:ring-white transition ease-in-out duration-150 hover:bg-green-600 shadow-md hover:shadow-lg transform hover:scale-110">
                                {{ $initials }}
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="rounded-xl shadow-2xl py-1 bg-white ring-1 ring-black ring-opacity-10">
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                                </div>
                                <x-dropdown-link :href="route('profile.edit')" class="hover:bg-gray-100 transition-colors duration-100"><i class="fa-solid fa-user-gear w-4 mr-2 text-gray-600"></i>Profil</x-dropdown-link>
                                <div class="border-t border-gray-200"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 hover:text-red-800 hover:bg-gray-100 transition-colors duration-100"><i class="fa-solid fa-right-from-bracket w-4 mr-2"></i>Log Out</x-dropdown-link>
                                </form>
                            </div>
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
                <x-responsive-nav-link :href="route('executive.summary')" :active="request()->routeIs('executive.summary')" class="text-white hover:bg-green-700/75">Executive Summary</x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:bg-green-700/75">Dashboard</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')" class="text-white hover:bg-green-700/75">Tugas Harian</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('special-assignments.index')" :active="request()->routeIs('special-assignments.*')" class="text-white hover:bg-green-700/75">SK Penugasan</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-green-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-300">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-white hover:bg-green-700/75">Profil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-300 hover:bg-green-700/75">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal About Us --}}
    <div x-show="showAboutModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center" x-cloak>
        <div @click.away="showAboutModal = false" class="relative mx-auto p-8 border w-full max-w-md shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
                <p class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-hand-sparkles mr-3 text-indigo-600"></i> Tentang Aplikasi Ini
                </p>
                <button type="button" @click="showAboutModal = false" class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="text-gray-700 text-base leading-relaxed mb-4">
                Aplikasi ini adalah sebuah platform manajemen proyek dan tugas yang dirancang untuk membantu tim mengelola alur kerja, melacak progres, dan meningkatkan kolaborasi secara efisien.
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-users-line mr-2 text-blue-600"></i> Tim Proyek Web
                </h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-700">
                        <tbody>
                     <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Project Manager</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Abdul Harist Habibullah</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Web Designer & Developer</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Arif Budi Setiawan</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Middle Man</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Galih Agan Pambayun</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Quality Assurance</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Rosalia Sianipar</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Web Developer</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Tegar Hidayat</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">UI Designer</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Srintika Yuni Kharisma</td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-200 text-sm text-gray-500 flex items-center">
                <i class="fas fa-code-branch mr-2"></i> Versi Aplikasi: <span class="font-semibold text-gray-700">1.0.0 (Beta)</span>
            </div>
            <div class="mt-1 text-sm text-gray-500 flex items-center">
                <i class="fas fa-copyright mr-2"></i> Dibuat oleh: <span class="font-semibold text-gray-700">PSI 2025</span>
            </div>
        </div>
    </div>
</nav>
