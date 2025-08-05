<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User: ') }} <span class="font-bold text-indigo-600">{{ $user->name }}</span>
        </h2>
    </x-slot>

    {{-- Latar belakang dan padding konsisten dengan halaman lain --}}
    <div class="py-8 bg-gray-50"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mengubah shadow-sm menjadi shadow-xl dan memastikan rounded-lg --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Pastikan users.partials.form-fields sudah di-styling dengan UI terbaru --}}
                        @include('users.partials.form-fields')

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6"> {{-- Menambahkan margin atas, border, dan padding atas --}}
                            <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium mr-6 transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-2"></i> Batal
                            </a>
                            {{-- Mengubah primary-button menjadi button dengan styling konsisten --}}
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i> {{ __('Update User') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>