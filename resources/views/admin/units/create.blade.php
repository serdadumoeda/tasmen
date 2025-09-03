<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Unit Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"> {{-- Latar belakang konsisten --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg"> {{-- Shadow dan rounded-lg konsisten --}}
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.units.store') }}" method="POST" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                        @csrf
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
                        {{-- Menggunakan partial form yang sama dengan edit --}}
                        @include('admin.units.partials.form-fields')
                        <div class="flex justify-end mt-8 border-t border-gray-200 pt-6">
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105 disabled:opacity-50"
                                    :disabled="isSubmitting">
                                <span x-show="!isSubmitting" class="inline-flex items-center">
                                    <i class="fas fa-save mr-2"></i> Simpan
                                </span>
                                <span x-show="isSubmitting" class="inline-flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>