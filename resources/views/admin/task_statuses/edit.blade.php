<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Status Tugas: ') }} {{ $status->label }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('admin.task-statuses.update', $status) }}" method="POST">
                    @method('PUT')
                    @include('admin.task_statuses._form', ['status' => $status])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
