<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Item Anggaran untuk Proyek: {{ $project->name }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('projects.budget-items.store', $project) }}" method="POST">
                        @csrf
                        @include('projects.budget._form', ['budgetItem' => new \App\Models\BudgetItem()])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>