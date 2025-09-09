<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Jabatan: {{ $jabatan->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ url('/admin/jabatans/' . $jabatan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nama Jabatan -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Jabatan</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $jabatan->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <!-- Role Jabatan -->
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Peran (Role)</label>
                            <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach($availableRoles as $role)
                                    <option value="{{ $role }}" @selected(old('role', $jabatan->role) == $role)>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Can Manage Users -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="can_manage_users" value="1" @checked(old('can_manage_users', $jabatan->can_manage_users))>
                                <span class="ml-2 text-sm text-gray-600">Dapat Mengelola Pengguna di Bawahnya</span>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            @if(isset($user) && $user)
                                <a href="{{ route('users.edit', $user) }}" class="mr-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Batal</a>
                            @else
                                <a href="{{ route('admin.units.edit', $unit) }}" class="mr-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Batal</a>
                            @endif
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
