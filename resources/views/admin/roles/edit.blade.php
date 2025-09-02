<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Peran: ') }} {{ $role->label }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                    @method('PUT')
                    @include('admin.roles._form', ['role' => $role])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
