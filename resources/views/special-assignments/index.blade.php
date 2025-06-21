<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar SK Penugasan') }}
            </h2>
            
            @can('create', App\Models\SpecialAssignment::class)
            <a href="{{ route('special-assignments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase shadow-sm">
                Tambah SK Baru
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Form Filter dan Pencarian -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <form action="{{ route('special-assignments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <input type="text" name="search" placeholder="Cari berdasarkan judul atau nomor SK..." value="{{ request('search') }}" class="rounded-md border-gray-300 shadow-sm col-span-1">
                    
                    @if(auth()->user()->canManageUsers())
                    <select name="personnel_id" class="rounded-md border-gray-300 shadow-sm col-span-1">
                        <option value="">-- Semua Personil --</option>
                        @foreach($subordinates as $user)
                            <option value="{{ $user->id }}" @selected(request('personnel_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @endif

                    <div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm">Filter</button>
                        <a href="{{ route('special-assignments.index') }}" class="px-4 py-2 text-gray-600">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Daftar SK -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200">
                    @forelse ($assignments as $sk)
                        <div class="p-6 hover:bg-gray-50/50 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-indigo-700">{{ $sk->title }}</h4>
                                    <div class="text-xs text-gray-500 mt-1 flex items-center space-x-2">
                                        <span>No. SK: {{ $sk->sk_number ?? '-' }}</span>
                                        <span>|</span>
                                        <span>Dibuat oleh: {{ $sk->creator->name ?? 'N/A' }}</span>
                                        {{-- Link Lihat File --}}
                                        @if ($sk->file_path)
                                            <span>|</span>
                                            <a href="{{ asset('storage/' . $sk->file_path) }}" target="_blank" class="text-blue-500 hover:underline flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                Lihat File
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm flex-shrink-0">
                                    @can('update', $sk)
                                    <a href="{{ route('special-assignments.edit', $sk) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="mt-3 border-t pt-3">
                                <h5 class="text-sm font-semibold mb-2">Anggota & Peran:</h5>
                                <ul class="space-y-1">
                                    @foreach($sk->members as $member)
                                        <li class="text-sm text-gray-700 ml-4">- {{ $member->name }} <span class="text-gray-500">({{ $member->pivot->role_in_sk }})</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 p-10">
                            <p>Tidak ada data SK ditemukan sesuai filter.</p>
                        </div>
                    @endforelse
                </div>
                 @if($assignments->hasPages())
                    <div class="p-6 border-t bg-gray-50">
                        {{ $assignments->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
