<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Alur Persetujuan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.approval-workflows.update', $workflow) }}" method="POST">
                        @method('PUT')
                        @include('admin.approval-workflows._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
