<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Proyek: ') }} {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Form menunjuk ke route 'projects.update' dengan method PUT --}}
                    <form action="{{ route('projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Menggunakan kembali partial form yang sama dengan halaman create --}}
                        {{-- Variabel $project dan $potentialMembers diteruskan dari ProjectController@edit --}}
                        @include('projects.partials.form', ['project' => $project, 'potentialMembers' => $potentialMembers])

                        <div class="flex items-center justify-between mt-6">
                            {{-- Tombol Batal kembali ke halaman detail proyek --}}
                            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                &larr; Batal dan Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Update Proyek
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>