<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Impor Pengguna & Unit Kerja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Oops!</strong>
                            <span class="block sm:inline">Ada beberapa masalah dengan input Anda.</span>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700">
                        <p class="font-bold">Instruksi Impor:</p>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            <li>File harus dalam format CSV.</li>
                            <li>Baris pertama (header) dari file CSV harus cocok dengan nama kolom di database.</li>
                            <li>Pastikan kolom NIP unik untuk setiap pengguna.</li>
                            <li>Data unit kerja (Eselon I, II, dll.) akan dibuat secara otomatis berdasarkan nama.</li>
                            <li>Pengguna yang sudah ada (berdasarkan NIP) akan diperbarui, yang baru akan dibuat.</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.users.import.handle') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="user_import" class="block text-sm font-medium text-gray-700">File CSV Pengguna</label>
                            <div class="mt-1 flex items-center">
                                <input type="file" name="user_import" id="user_import" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <a href="{{ route('users.index') }}" class="text-gray-600 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Impor Data
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
