<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rencana Anggaran Biaya (RAB)</h2>
                <p class="text-sm text-gray-500">Proyek: {{ $project->name }}</p>
            </div>
            @can('update', $project)
            <a href="{{ route('projects.budget-items.create', $project) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase">Tambah Item</a>
            @endcan
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- PENAMBAHAN KODE: Link untuk kembali --}}
            <div class="mb-4">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Detail Proyek
                </a>
            </div>
            {{-- AKHIR PENAMBAHAN --}}

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-6 rounded-lg shadow-sm mb-6">
                <p class="text-lg">Total Anggaran Proyek</p>
                <p class="text-4xl font-bold">Rp {{ number_format($totalBudget, 2, ',', '.') }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    @php
                        // Definisikan nama kategori di sini agar tidak memanggil model dari view
                        $categoryNames = \App\Models\BudgetItem::CATEGORIES;
                    @endphp
                    @forelse ($budgetItems as $category => $items)
                        <div class="border border-gray-200 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 px-4 py-3 bg-gray-50 border-b">{{ $categoryNames[$category] ?? $category }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uraian</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Detail</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Biaya</th>
                                            @can('update', $project)<th class="px-6 py-3"></th>@endcan
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->description }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ $item->quantity }} x {{ $item->frequency }} x Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">Rp {{ number_format($item->total_cost, 2, ',', '.') }}</td>
                                            @can('update', $project)
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('projects.budget-items.edit', [$project, $item]) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form action="{{ route('projects.budget-items.destroy', [$project, $item]) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Yakin ingin hapus item ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                            @endcan
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-10">
                                <p>Belum ada item anggaran untuk proyek ini.</p>
                                @can('update', $project)
                                <a href="{{ route('projects.budget-items.create', $project) }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase">Mulai Tambah Item Anggaran</a>
                                @endcan
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>