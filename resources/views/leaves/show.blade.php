<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Permintaan Cuti') }}
        </h2>
    </x-slot>

    <div x-data="{ activeTab: 'details' }" class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 bg-white border-b border-gray-200">

                    {{-- Navigasi Tab --}}
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'details'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'details' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                Detail Cuti
                            </button>
                            <button @click="activeTab = 'surat'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'surat', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'surat' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                Persuratan
                            </button>
                        </nav>
                    </div>

                    {{-- Konten Tab --}}
                    <div>
                        {{-- Tab Detail Cuti --}}
                        <div x-show="activeTab === 'details'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Data Pegawai</h3>
                                    <p><span class="font-semibold">Nama:</span> {{ $leaveRequest->user->name }}</p>
                                    <p><span class="font-semibold">Jabatan:</span> {{ $leaveRequest->user->jabatan->name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Unit Kerja:</span> {{ $leaveRequest->user->unit->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Detail Cuti</h3>
                                    <p><span class="font-semibold">Jenis Cuti:</span> {{ $leaveRequest->leaveType->name }}</p>
                                    <p><span class="font-semibold">Tanggal:</span> {{ $leaveRequest->start_date->format('d M Y') }} s/d {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                    <p><span class="font-semibold">Durasi:</span> {{ $leaveRequest->duration_days }} hari</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p><span class="font-semibold">Alasan:</span></p>
                                    <p class="mt-1 text-gray-600">{{ $leaveRequest->reason }}</p>
                                </div>
                                @if($leaveRequest->attachment_path)
                                <div class="md:col-span-2">
                                     <p><span class="font-semibold">Lampiran:</span> <a href="{{ route('leaves.attachment', $leaveRequest) }}" class="text-indigo-600 hover:underline" target="_blank">Lihat Lampiran</a></p>
                                </div>
                                @endif
                            </div>

                            @if($leaveRequest->current_approver_id === Auth::id())
                            <div class="mt-8 border-t pt-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-4">Tindakan Persetujuan</h3>
                                <div class="flex items-start space-x-4">
                                    <form action="{{ route('leaves.approve', $leaveRequest) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                            {{ __('Setujui') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('leaves.reject', $leaveRequest) }}" method="POST" class="w-full">
                                        @csrf
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-grow">
                                                <label for="rejection_reason" class="sr-only">Alasan Penolakan</label>
                                                <textarea name="rejection_reason" id="rejection_reason" rows="2" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Tulis alasan penolakan di sini..."></textarea>
                                            </div>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                                {{ __('Tolak') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Tab Persuratan --}}
                        <div x-show="activeTab === 'surat'" x-cloak>
                            <div class="p-4">
                                @include('projects.partials.persuratan', ['suratList' => $leaveRequest->surat, 'suratable' => $leaveRequest])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
