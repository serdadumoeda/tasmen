<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Anggaran & Realisasi
                </h2>
                <p class="text-sm text-gray-500">Proyek: {{ $project->name }}</p>
            </div>
            @can('update', $project)
            <a href="{{ route('projects.budget-items.create', $project) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase shadow-sm">
                Tambah Item Anggaran
            </a>
            @endcan
        </div>
    </x-slot>

    {{-- Gunakan Alpine.js untuk mengelola state modal --}}
    <div x-data="{
        showModal: false,
        budgetItemId: null,
        formAction: '',
        setItem(id) {
            this.budgetItemId = id;
            this.formAction = '{{ url('budget-items') }}/' + id + '/realizations';
            this.showModal = true;
        }
    }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Detail Proyek
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Anggaran (Rencana)</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalBudget, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Realisasi</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">Rp {{ number_format($totalRealization, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Sisa Anggaran</p>
                    @php $remainingTotal = $totalBudget - $totalRealization; @endphp
                    <p class="text-3xl font-bold mt-1 {{ $remainingTotal < 0 ? 'text-red-700' : 'text-green-600' }}">
                        Rp {{ number_format($remainingTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-8">
                    @php
                        $categoryNames = \App\Models\BudgetItem::CATEGORIES;
                    @endphp
                    @forelse ($budgetItems->all() as $category => $items)
                        <div class="border border-gray-200 rounded-lg" x-data="{ open: true }">
                            <h3 @click="open = !open" class="text-lg font-medium text-gray-900 px-4 py-3 bg-gray-50 border-b border-gray-200 cursor-pointer flex justify-between items-center">
                                <span>{{ $categoryNames[$category] ?? $category }}</span>
                                <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': !open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </h3>
                            <div x-show="open" x-transition class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uraian</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Anggaran (Rencana)</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa</th>
                                            @can('update', $project)<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>@endcan
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($items as $item)
                                        <tr x-data="{ showDetails: false }">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->item_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->quantity }}x{{ $item->frequency }} @ Rp{{ number_format($item->unit_price, 0, ',', '.') }}</div>
                                                @if($item->realizations->count() > 0)
                                                    <button @click="showDetails = !showDetails" class="text-xs text-blue-500 hover:underline mt-1">
                                                        <span x-show="!showDetails">Lihat Transaksi ({{ $item->realizations->count() }})</span>
                                                        <span x-show="showDetails">Sembunyikan Transaksi</span>
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">Rp {{ number_format($item->total_cost, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-red-600">Rp {{ number_format($item->realized_cost, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium {{ $item->remaining_cost < 0 ? 'text-red-700' : 'text-green-600' }}">
                                                Rp {{ number_format($item->remaining_cost, 2, ',', '.') }}
                                            </td>
                                            @can('update', $project)
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="setItem({{ $item->id }})" class="text-green-600 hover:text-green-900 font-semibold">Catat Realisasi</button>
                                                <a href="{{ route('projects.budget-items.edit', [$project, $item]) }}" class="text-indigo-600 hover:text-indigo-900 ml-3">Edit</a>
                                                <form action="{{ route('projects.budget-items.destroy', [$project, $item]) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('Yakin ingin hapus item anggaran ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                            @endcan
                                        </tr>
                                        {{-- Baris untuk menampilkan detail transaksi --}}
                                        <tr x-show="showDetails" x-transition>
                                            <td colspan="5" class="p-0">
                                                <div class="bg-gray-50 p-4">
                                                    <h4 class="font-semibold text-xs text-gray-700 mb-2">Riwayat Transaksi untuk "{{ $item->item_name }}"</h4>
                                                    <ul class="divide-y divide-gray-200">
                                                        @forelse($item->realizations as $realization)
                                                            <li class="py-2 flex justify-between items-center text-xs">
                                                                <div>
                                                                    <span class="text-gray-800">{{ $realization->transaction_date->format('d M Y') }}: {{ $realization->description ?? 'Pengeluaran' }}</span>
                                                                    <span class="text-gray-500 block">Dicatat oleh: {{ $realization->user->name }}</span>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <span class="font-medium text-red-600">Rp {{ number_format($realization->amount, 2, ',', '.') }}</span>
                                                                    @can('update', $project)
                                                                    <form action="{{ route('budget-realizations.destroy', $realization) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="ml-3 text-gray-400 hover:text-red-500">&times;</button>
                                                                    </form>
                                                                    @endcan
                                                                </div>
                                                            </li>
                                                        @empty
                                                            <li class="text-gray-500">Belum ada transaksi.</li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-10">
                            <p>Belum ada item anggaran untuk proyek ini.</p>
                            @can('update', $project)
                            <a href="{{ route('projects.budget-items.create', $project) }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase">Mulai Tambah Item Anggaran</a>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div x-show="showModal"
             style="display: none;"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75">
            <div @click.away="showModal = false" class="bg-white rounded-lg shadow-xl overflow-hidden transform transition-all sm:max-w-lg sm:w-full">
                <form :action="formAction" method="POST">
                    @csrf
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Catat Realisasi Baru</h3>
                        <p class="text-sm text-gray-500">Catat pengeluaran untuk item anggaran.</p>
                    </div>
                    <div class="px-6 py-5 bg-gray-50 space-y-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                            <input type="number" name="amount" id="amount" step="0.01" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="transaction_date" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" id="transaction_date" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-3 bg-gray-100 text-right space-x-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Simpan Realisasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>