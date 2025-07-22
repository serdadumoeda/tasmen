<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.units.update', $unit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.units.partials.form-fields', ['unit' => $unit])
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
