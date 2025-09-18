<li class="flex items-center justify-between bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors duration-150" id="jabatan-{{ $jabatan->id }}">
    <div class="flex-grow">
        <div class="flex items-center">
            <p class="font-semibold text-gray-800">{{ $jabatan->name }}</p>
            @if($jabatan->can_manage_users)
                <span class="ml-3 text-xs font-semibold inline-flex items-center px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                    <i class="fas fa-users-cog mr-1.5 opacity-80"></i> Dapat Mengelola Pengguna
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-500 mt-1">
            @if($jabatan->user)
                <i class="fas fa-user-check text-green-500 mr-2"></i>Diisi oleh: {{ $jabatan->user->name }}
            @else
                <i class="fas fa-user-clock text-yellow-500 mr-2"></i>Jabatan Kosong
            @endif
        </p>
    </div>
    <div class="flex items-center space-x-4 ml-4 flex-shrink-0">
        <a href="{{ route('admin.jabatans.edit', $jabatan) }}" class="font-medium text-indigo-600 hover:text-indigo-800 transition-colors duration-150">Edit</a>
        @if(!$jabatan->user_id)
            <form action="{{ route('admin.jabatans.destroy', $jabatan) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jabatan ini?');" class="inline-block form-delete-jabatan">
                @csrf
                @method('DELETE')
                <button type="submit" class="font-medium text-red-600 hover:text-red-800 transition-colors duration-150">Hapus</button>
            </form>
        @else
             <span class="font-medium text-gray-400 cursor-not-allowed" title="Jabatan ini tidak dapat dihapus karena ada pengguna yang menempatinya.">Hapus</span>
        @endif
    </div>
</li>
