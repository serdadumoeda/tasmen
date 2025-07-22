<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-lg font-bold">Daftar Unit</h1>
                        <a href="{{ route('admin.units.create') }}" class="px-4 py-2 bg-green-500 text-white rounded-md">
                            Tambah Unit
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">Nama</th>
                                <th class="py-2 px-4 border-b">Level</th>
                                <th class="py-2 px-4 border-b">Unit Atasan</th>
                                <th class="py-2 px-4 border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td class="py-2 px-4 border-b">{{ $unit->name }}</td>
                                    <td class="py-2 px-4 border-b">{{ $unit->level }}</td>
                                    <td class="py-2 px-4 border-b">{{ $unit->parentUnit->name ?? '-' }}</td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="{{ route('admin.units.edit', $unit) }}" class="text-blue-500">Edit</a>
                                        <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
