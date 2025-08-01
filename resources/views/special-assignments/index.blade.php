<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar SK Penugasan') }}
            </h2>
            
            @can('create', App\Models\SpecialAssignment::class)
            <a href="{{ route('special-assignments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-plus-circle mr-2"></i> Tambah SK Baru
            </a>
            @endcan
        </div>
    </x-slot>

    {{-- Hapus bg-gray-50 dan flex-grow karena sekarang diatur oleh main di app.blade.php --}}
    <div class="py-8"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-xl mb-6">
                <form action="{{ route('special-assignments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <input type="text" name="search" placeholder="Cari berdasarkan judul atau nomor SK..." value="{{ request('search') }}" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                    
                    @if(auth()->user()->canManageUsers())
                    <select name="personnel_id" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                        <option value="">-- Semua Personil --</option>
                        @foreach($subordinates as $user)
                            <option value="{{ $user->id }}" @selected(request('personnel_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @endif

                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <a href="{{ route('special-assignments.index') }}" class="inline-flex items-center px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-redo mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="divide-y divide-gray-200">
                    @forelse ($assignments as $sk)
                        <div class="p-6 hover:bg-blue-50/50 transition duration-200 ease-in-out border-b border-gray-100 last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-xl text-indigo-700 mb-1">{{ $sk->title }}</h4>
                                    <div class="text-sm text-gray-600 mt-1 flex items-center space-x-3">
                                        <span class="inline-flex items-center"><i class="fas fa-hashtag text-gray-400 mr-1"></i> No. SK: {{ $sk->sk_number ?? '-' }}</span>
                                        <span class="inline-flex items-center"><i class="fas fa-user-edit text-gray-400 mr-1"></i> Dibuat oleh: {{ $sk->creator->name ?? 'N/A' }}</span>
                                        @if ($sk->file_path)
                                            <a href="{{ asset('storage/' . $sk->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center font-medium">
                                                <i class="fas fa-file-alt mr-1"></i> Lihat File
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm flex-shrink-0 flex space-x-2">
                                    @can('update', $sk)
                                    <a href="{{ route('special-assignments.edit', $sk) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm hover:shadow-md">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-200 pt-4">
                                <h5 class="text-base font-semibold mb-2 flex items-center"><i class="fas fa-users-line mr-2 text-purple-600"></i> Anggota & Peran:</h5>
                                <ul class="space-y-1">
                                    @foreach($sk->members as $member)
                                        <li class="text-sm text-gray-700 ml-6 flex items-center"><i class="fas fa-user-tag text-gray-400 mr-2"></i> {{ $member->name }} <span class="text-gray-500 ml-1">({{ $member->pivot->role_in_sk }})</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 p-10">
                            <p class="text-lg mb-4">Tidak ada data SK ditemukan sesuai filter.</p>
                            @can('create', App\Models\SpecialAssignment::class)
                            <a href="{{ route('special-assignments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                <i class="fas fa-plus-circle mr-2"></i> Buat SK Penugasan Pertama Anda
                            </a>
                            @endcan
                        </div>
                    @endforelse
                </div>
                @if($assignments->hasPages())
                    <div class="p-6 border-t border-gray-200 bg-gray-50">
                        {{ $assignments->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>