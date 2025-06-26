<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                @if(Auth::user()->isTopLevelManager())
                    <x-nav-link :href="route('executive.summary')" :active="request()->routeIs('executive.summary')">
                        {{ __('Executive Summary') }}
                    </x-nav-link>
                @else
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                @endif

                    {{-- PERBAIKAN: Mengembalikan link Tugas Harian yang hilang --}}
                    <x-nav-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">
                        {{ __('Tugas Harian') }}
                    </x-nav-link>
                    
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('projects.show*') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300' }}">
                                    <div>Proyek Saya</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Akses Cepat Proyek') }}
                                </div>
                                @forelse ($quickProjects as $project)
                                    <x-dropdown-link :href="route('projects.show', $project)">
                                        {{ Str::limit($project->name, 25) }}
                                    </x-dropdown-link>
                                @empty
                                    <div class="block px-4 py-2 text-sm text-gray-500">
                                        Anda belum menjadi anggota proyek.
                                    </div>
                                @endforelse
                                @can('create', App\Models\Project::class)
                                <div class="border-t border-gray-200"></div>
                                <x-dropdown-link :href="route('projects.create')">
                                    + Buat Proyek Baru
                                </x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>

                    @if(Auth::user()->canManageUsers())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index*')">
                            {{ __('User Management') }}
                        </x-nav-link>
                    @endif
                    
                    @if(Auth::user()->isTopLevelManager())
                        <x-nav-link :href="route('workload.analysis')" :active="request()->routeIs('workload.analysis')">
                            {{ __('Analisis Beban Kerja') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="60">
                        <x-slot name="trigger">
                            <button class="relative inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 hover:text-gray-700 rounded-lg focus:outline-none">
                                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
                                <span class="sr-only">View notifications</span>
                                @if(auth()->user() && auth()->user()->unreadNotifications->count() > 0)
                                    <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -end-1">{{ auth()->user()->unreadNotifications->count() }}</div>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs text-gray-600 bg-gray-50">
                                Notifikasi ({{ auth()->user()->unreadNotifications->count() }})
                            </div>
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                <a href="{{ $notification->data['url'] ?? '#' }}?notification_id={{ $notification->id }}" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                    {{ Str::limit($notification->data['message'], 50) }}
                                </a>
                            @empty
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Tidak ada notifikasi baru.
                                </div>
                            @endforelse
                        </x-slot>
                    </x-dropdown>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('special-assignments.index')">
                            {{ __('SK Penugasan Saya') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- PERBAIKAN: Mengembalikan link Tugas Harian yang hilang (Mobile) --}}
            <x-responsive-nav-link :href="route('adhoc-tasks.index')" :active="request()->routeIs('adhoc-tasks.*')">
                {{ __('Tugas Harian') }}
            </x-responsive-nav-link>
            
            @if(Auth::user()->canManageUsers())
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index*')">
                    {{ __('User Management') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isTopLevelManager())
                <x-responsive-nav-link :href="route('workload.analysis')" :active="request()->routeIs('workload.analysis')">
                    {{ __('Analisis Beban Kerja') }}
                </x-responsive-nav-link>
            @endif
        </div>
        
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">Proyek Saya</div>
            </div>
            <div class="mt-3 space-y-1">
                @forelse ($quickProjects as $project)
                    <x-responsive-nav-link :href="route('projects.show', $project)" :active="request()->is('projects/' . $project->id)">
                        {{ $project->name }}
                    </x-responsive-nav-link>
                @empty
                    <p class="px-4 text-sm text-gray-500">Belum ada proyek.</p>
                @endforelse
                 @can('create', App\Models\Project::class)
                    <x-responsive-nav-link :href="route('projects.create')">
                        + Buat Proyek Baru
                    </x-responsive-nav-link>
                @endcan
            </div>
        </div>


        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('special-assignments.index')">
                    {{ __('SK Penugasan Saya') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>