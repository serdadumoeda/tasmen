<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name ?? '')" required autofocus autocomplete="name" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email ?? '')" required autocomplete="username" />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="role" :value="__('Role')" />
    <select name="role" id="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="user" @selected(old('role', $user->role ?? '') == 'user')>User</option>
        <option value="leader" @selected(old('role', $user->role ?? '') == 'leader')>Leader</option>
        <option value="manager" @selected(old('role', $user->role ?? '') == 'manager')>Manager</option>
        <option value="superadmin" @selected(old('role', $user->role ?? '') == 'superadmin')>Super Admin</option>
    </select>
    <x-input-error :messages="$errors->get('role')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="password" :value="__('Password')" />
    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
    @if(isset($user))
        <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
    @endif
</div>

<div class="mt-4">
    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
</div>