@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert">
        <div class="flex items-start">
            <div class="flex-shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle h-5 w-5 text-red-500"></i>
            </div>
            <div class="ml-3">
                <strong class="font-bold">Oops! Ada yang salah:</strong>
                 <ul class="mt-1.5 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- Consistent gap with other forms --}}
    {{-- Kolom Kiri --}}
    <div>
        <div class="mb-6"> {{-- Consistent spacing between form groups --}}
            <label for="name" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-user mr-2 text-gray-500"></i> Nama Lengkap Pengguna <span class="text-red-500">*</span>
            </label>
            <input id="name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required autofocus />
            @error('name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6"> {{-- Consistent spacing --}}
            <label for="email" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-envelope mr-2 text-gray-500"></i> Email <span class="text-red-500">*</span>
            </label>
            <input id="email" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required />
            @error('email') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>
        
        <div class="mb-6"> {{-- Consistent spacing --}}
            <label for="role" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-user-tag mr-2 text-gray-500"></i> Level Jabatan (Role) <span class="text-red-500">*</span>
            </label>
            <select name="role" id="role" required class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                @php
                    $roles = [
                        App\Models\User::ROLE_SUPERADMIN, // Assuming Superadmin is a possible role
                        App\Models\User::ROLE_ESELON_I,
                        App\Models\User::ROLE_ESELON_II,
                        App\Models\User::ROLE_KOORDINATOR,
                        App\Models\User::ROLE_SUB_KOORDINATOR,
                        App\Models\User::ROLE_STAF
                    ];
                @endphp
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') == $role)>{{ $role }}</option>
                @endforeach
            </select>
            @error('role') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label for="unit_id" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-building-user mr-2 text-gray-500"></i> Unit Kerja <span class="text-red-500">*</span>
            </label>
            <select name="unit_id" id="unit_id" required class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                <option value="">-- Pilih Unit Kerja --</option>
                @foreach($units as $unitOption)
                    <option value="{{ $unitOption->id }}" @selected(old('unit_id', $user->unit_id ?? '') == $unitOption->id)>
                        {{ $unitOption->name }}
                    </option>
                @endforeach
            </select>
            @error('unit_id') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Kolom Kanan --}}
    <div>
        <div class="mb-6">
            <label for="password" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-lock mr-2 text-gray-500"></i> Password
                @if(!isset($user))<span class="text-red-500">*</span>@endif
            </label>
            <input id="password" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" type="password" name="password" @if(!isset($user)) required @endif />
            @error('password') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            @if(isset($user))
                <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
            @endif
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-lock-open mr-2 text-gray-500"></i> Konfirmasi Password
            </label>
            <input id="password_confirmation" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" type="password" name="password_confirmation" />
            @error('password_confirmation') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label for="status" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-circle-dot mr-2 text-gray-500"></i> Status <span class="text-red-500">*</span>
            </label>
            <select name="status" id="status" required class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                <option value="active" @selected(old('status', $user->status ?? 'active') == 'active')>Aktif</option>
                <option value="suspended" @selected(old('status', $user->status ?? '') == 'suspended')>Ditangguhkan</option>
            </select>
            @error('status') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@push('scripts')
{{-- No longer needed --}}
@endpush