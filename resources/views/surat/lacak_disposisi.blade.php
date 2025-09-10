<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('surat.index') }}" class="text-gray-400 hover:text-gray-600">Daftar Surat</a> /
                    <a href="{{ route('surat.show', $surat) }}" class="text-gray-400 hover:text-gray-600">Detail</a> /
                    <span class="font-bold">Lacak Disposisi</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Hierarki alur disposisi untuk surat: "{{ $surat->perihal }}"</p>
            </div>
            <a href="{{ route('surat.show', $surat) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 shadow-md">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Surat
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-xl">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-3">
                    <i class="fas fa-sitemap mr-2 text-indigo-600"></i>
                    Pohon Disposisi
                </h3>

                @if ($disposisiTree->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500">Surat ini belum memiliki alur disposisi.</p>
                    </div>
                @else
                    <ul class="space-y-2">
                        @foreach ($disposisiTree as $item)
                            {{-- This Blade component recursively calls itself to build the tree --}}
                            <x-disposisi-item :item="$item" :level="0" />
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
