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
<nav x-data="{ open: false, showAboutModal: false }" class="bg-[#00796B] border-b border-green-800">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Left Section: Logo -->
            <div class="flex items-center">
                <div class="shrink-0">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>
            </div>

            <!-- Center Section: Navigation Links -->
            <div class="flex-1 flex justify-center items-center">
                <div class="hidden space-x-6 sm:flex items-center">

                    {{-- Dasbor --}}
                    <div class="nav-item-container">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard', 'executive.summary')" class="nav-icon-link">
                            <i class="fas fa-tachometer-alt text-xl"></i>
                        </x-nav-link>
                        <div class="nav-tooltip">{{ __('Dasbor') }}</div>
                    </div>

                    {{-- Dropdown Menu Kerja --}}
                    <div class="nav-item-container">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="nav-icon-link inline-flex items-center justify-center text-white focus:outline-none transition duration-150 ease-in-out {{ (request()->routeIs(['global.dashboard', 'adhoc-tasks.*', 'special-assignments.*', 'projects.*'])) && !request()->routeIs('executive.summary') ? 'active' : '' }}">
                                    <i class="fas fa-briefcase text-xl"></i>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400">Kegiatan</div>
                                <x-dropdown-link :href="route('global.dashboard')">Kegiatan</x-dropdown-link>
                                <div class="border-t border-gray-100"></div>
                                <div class="px-4 py-2 text-xs text-gray-400">Tugas</div>
                                <x-dropdown-link :href="route('adhoc-tasks.index')">Tugas Harian</x-dropdown-link>
                                <x-dropdown-link :href="route('special-assignments.index')">SK Penugasan</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                        <div class="nav-tooltip">Kerja</div>
                    </div>

                    {{-- Dropdown Surat --}}
                    <div class="nav-item-container">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="nav-icon-link inline-flex items-center justify-center text-white focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(['surat.*', 'admin.klasifikasi.*', 'arsip.*']) ? 'active' : '' }}">
                                    <i class="fas fa-envelope-open-text text-xl"></i>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400">Surat</div>
                                <x-dropdown-link :href="route('surat.index')">Daftar Surat</x-dropdown-link>
                                <x-dropdown-link :href="route('arsip.index')">Arsip Digital</x-dropdown-link>
                                @if(Auth::user()->isSuperAdmin())
                                <div class="border-t border-gray-100"></div>
                                <x-dropdown-link :href="route('admin.klasifikasi.index')">Manajemen Klasifikasi</x-dropdown-link>
                                @endif
                            </x-slot>
                        </x-dropdown>
                        <div class="nav-tooltip">Surat</div>
                    </div>

                    {{-- Menu Cuti --}}
                    <div class="nav-item-container">
                        <x-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.*')" class="nav-icon-link">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </x-nav-link>
                        <div class="nav-tooltip">{{ __('Cuti') }}</div>
                    </div>

                    {{-- Dropdown Laporan & Analisis --}}
                    @if (Auth::user()->isTopLevelManager())
                        <div class="nav-item-container">
                            <x-dropdown align="left" width="60">
                                <x-slot name="trigger">
                                    <button class="nav-icon-link inline-flex items-center justify-center text-white focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(['workload.analysis.*', 'weekly-workload.*']) ? 'active' : '' }}">
                                        <i class="fas fa-chart-pie text-xl"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('workload.analysis')">Analisis Beban Kerja</x-dropdown-link>
                                    <x-dropdown-link :href="route('weekly-workload.index')">Beban Kerja Mingguan</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <div class="nav-tooltip">Laporan & Analisis</div>
                        </div>
                    @endif

                    {{-- Dropdown Manajemen Tim --}}
                    @if(Auth::user()->canManageUsers())
                        <div class="nav-item-container">
                            <x-dropdown align="left" width="60">
                                <x-slot name="trigger">
                                    <button class="nav-icon-link inline-flex items-center justify-center text-white focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(['users.*', 'admin.units.*', 'resource-pool.*', 'peminjaman-requests.*']) && !request()->routeIs('admin.api_keys.*', 'admin.activities.index') ? 'active' : '' }}">
                                        <i class="fas fa-users-cog text-xl"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="px-4 py-2 text-xs text-gray-400">Manajemen</div>
                                    @if(Auth::user()->isSuperAdmin())
                                        <x-dropdown-link :href="route('admin.units.index')">Manajemen Unit</x-dropdown-link>
                                    @endif
                                    <x-dropdown-link :href="route('users.index')">Manajemen Pengguna</x-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <div class="px-4 py-2 text-xs text-gray-400">Sumber Daya</div>
                                    <x-dropdown-link :href="route('peminjaman-requests.my-requests')">Penugasan Anggota</x-dropdown-link>
                                    <x-dropdown-link :href="route('resource-pool.index')">Resource Pool</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <div class="nav-tooltip">Tim</div>
                        </div>
                    @endif

                    {{-- Dropdown Pengaturan Sistem (Hanya untuk Superadmin) --}}
                    @if(Auth::user()->isSuperAdmin())
                        <div class="nav-item-container">
                            <x-dropdown align="left" width="60">
                                <x-slot name="trigger">
                                    <button class="nav-icon-link inline-flex items-center justify-center text-white focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(['admin.settings.*', 'admin.api_keys.*', 'admin.activities.index', 'admin.approval-workflows.*', 'admin.cuti-bersama.*']) ? 'active' : '' }}">
                                        <i class="fas fa-cogs text-xl"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="px-4 py-2 text-xs text-gray-400">Umum</div>
                                    <x-dropdown-link :href="route('admin.settings.index')">Pengaturan Umum</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.settings.formulas')">Pengaturan Rumus</x-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <div class="px-4 py-2 text-xs text-gray-400">Manajemen Cuti</div>
                                    @if(Auth::user()->canManageLeaveSettings())
                                        <x-dropdown-link :href="route('admin.approval-workflows.index')">Manajemen Alur Persetujuan</x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.cuti-bersama.index')">Manajemen Cuti Bersama</x-dropdown-link>
                                        <div class="border-t border-gray-100"></div>
                                    @endif
                                    <div class="px-4 py-2 text-xs text-gray-400">Sistem</div>
                                    <x-dropdown-link :href="route('admin.api_keys.index')">Manajemen Integrasi</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.api_keys.query_helper')">API Query Helper</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.activities.index')">Log Aktivitas</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <div class="nav-tooltip">Pengaturan</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Section: User Profile & Actions -->
            <div class="hidden sm:flex sm:items-center">
                {{-- Dropdown Notifikasi --}}
                <div class="relative" x-data="notifications()" x-init="fetchUnread()">
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
                                    <div class="font-medium text-sm text-gray-500 break-words">{{ Auth::user()->email }}</div>
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

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-green-800 focus:outline-none focus:bg-green-800 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Responsive Navigation Menu --}}
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
                Aplikasi ini adalah sebuah platform manajemen kegiatan dan tugas yang dirancang untuk membantu tim mengelola alur kerja, melacak progres, dan meningkatkan kolaborasi secara efisien.
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-users-line mr-2 text-blue-600"></i> Tim Kegiatan Web
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
                            <td class="py-2 px-1 font-semibold text-gray-600">Quality Assurance</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Meta Lara Pandini</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Devops</td>
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
