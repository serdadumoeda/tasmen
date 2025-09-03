<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit SK Penugasan') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div x-data="{ activeTab: 'edit' }" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button @click="activeTab = 'edit'"
                                :class="{
                                    'border-indigo-500 text-indigo-600': activeTab === 'edit',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'edit'
                                }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                            <i class="fas fa-pencil-alt mr-2"></i> Edit Penugasan
                        </button>
                        <button @click="activeTab = 'persuratan'"
                                :class="{
                                    'border-indigo-500 text-indigo-600': activeTab === 'persuratan',
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'persuratan'
                                }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                            <i class="fas fa-envelope-open-text mr-2"></i> Persuratan
                        </button>
                    </nav>
                </div>

                <div class="p-6 text-gray-900">
                    <div x-show="activeTab === 'edit'" x-cloak>
                        <form action="{{ route('special-assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            @include('special-assignments._form')
                        </form>
                    </div>
                    <div x-show="activeTab === 'persuratan'" x-cloak>
                        @include('projects.partials.persuratan', ['model' => $assignment])
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>