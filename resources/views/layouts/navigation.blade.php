<nav x-data="{ open: false }" class="bg-[#00796B] border-b border-green-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <img src="{{ asset('images/logo-kemnaker.png') }}" alt="Logo" class="block h-9 w-auto">
                        <span class="text-white text-lg font-bold">Tugas Kita</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(Auth::user()->isTopLevelManager())
                        <x-nav-link :href="route('executive.summary')" :active="request()->routeIs('executive.summary')">
                            Executive Summary
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">
                        Tugas Harian
                    </x-nav-link>

                    {{-- ========================================================== --}}
                    {{-- =============      MENU YANG DITAMBAHKAN KEMBALI      ============ --}}
                    {{-- ========================================================== --}}
                    <x-nav-link :href="route('special-assignments.index')" :active="request()->routeIs('special-assignments.*')">
                        SK Penugasan
                    </x-nav-link>

                    @if(Auth::user()->role != 'staf') 
                    <x-nav-link :href="route('peminjaman-requests.index')" :active="request()->routeIs('peminjaman-requests.index')">
                        {{ __('Persetujuan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('resource-pool.index')" :active="request()->routeIs('resource-pool.index')">
                        {{ __('Resource Pool') }}
                    </x-nav-link>
                    @endif
                    

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('projects.*') ? 'border-yellow-300 text-white' : 'border-transparent text-white hover:text-gray-200 hover:border-yellow-300/75 focus:text-gray-200 focus:border-yellow-300/75' }}">
                                    <div>Proyek Saya</div>
                                    <div class="ms-1 fill-current h-4 w-4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="block px-4 py-2 text-xs text-gray-400">Akses Cepat Proyek</div>
                                @forelse ($quickProjects as $project)
                                    <x-dropdown-link :href="route('projects.show', $project)">{{ Str::limit($project->name, 30) }}</x-dropdown-link>
                                @empty
                                    <div class="px-4 py-2 text-sm text-gray-500">Belum ada proyek.</div>
                                @endforelse
                                @can('create', App\Models\Project::class)
                                <div class="border-t border-gray-200"></div>
                                <x-dropdown-link :href="route('projects.create.step1')" class="font-semibold text-blue-600">
                                    <i class="fa-solid fa-plus-circle mr-2"></i>Buat Proyek Baru
                                </x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-[#00796B] hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1 fill-current h-4 w-4"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')"><i class="fa-solid fa-user-gear w-4 mr-2"></i>{{ __('Profile') }}</x-dropdown-link>
                        @if(Auth::user()->canManageUsers())
                            <x-dropdown-link :href="route('users.index')"><i class="fa-solid fa-users w-4 mr-2"></i>{{ __('User Management') }}</x-dropdown-link>
                        @endif
                        @if(Auth::user()->isTopLevelManager())
                            <x-dropdown-link :href="route('workload.analysis')"><i class="fa-solid fa-chart-line w-4 mr-2"></i>{{ __('Beban Kerja') }}</x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('weekly-workload.index')">
                            <i class="fa-solid fa-tachometer-alt fa-fw w-4 mr-2"></i>Beban Kerja Mingguan
                        </x-dropdown-link>
                        <div class="border-t border-gray-200"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600"><i class="fa-solid fa-right-from-bracket w-4 mr-2"></i>{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-green-800 focus:outline-none focus:bg-green-800 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">{{ __('Tugas Harian') }}</x-responsive-nav-link>
      
            <x-responsive-nav-link :href="route('special-assignments.index')" :active="request()->routeIs('special-assignments.*')">{{ __('SK Penugasan') }}</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-green-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-300">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>