<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Cuti') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-secondary-button :href="route('leaves.workflow')">
                    <i class="fas fa-sitemap mr-2"></i>
                    Lihat Alur Kerja
                </x-secondary-button>
                <a href="{{ route('leaves.calendar') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-calendar-alt mr-2"></i> {{ __('Lihat Kalender') }}
                </a>
                <a href="{{ route('leaves.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <i class="fas fa-plus-circle mr-2"></i> {{ __('Ajukan Cuti Baru') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Approval Requests -->
            @if($approvalRequests->isNotEmpty())
                <x-card>
                    <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Permintaan Persetujuan Tim') }}</h3>

                    <!-- Filters -->
                    <form action="{{ route('leaves.index') }}" method="GET" class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="filter_unit" class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                                <select name="filter_unit" id="filter_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Semua Unit</option>
                                    @foreach($unitsInHierarchy as $unit)
                                        <option value="{{ $unit->id }}" {{ request('filter_unit') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filter_status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="filter_status" id="filter_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('filter_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved_by_supervisor" {{ request('filter_status') == 'approved_by_supervisor' ? 'selected' : '' }}>Approved by Supervisor</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Filter</button>
                                <a href="{{ route('leaves.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Cuti</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($approvalRequests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->leaveType->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $request->duration_days }} hari</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('leaves.show', $request) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                                Lihat Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif

            <!-- My Leave Requests -->
            <x-card>
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Riwayat Pengajuan Cuti Saya') }}</h3>

                <!-- Leave Balance Summary -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-md text-gray-700 mb-3">Ringkasan Cuti Tahunan {{ $annualLeaveBalance->year }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-500">Sisa Cuti Tahun Lalu</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $annualLeaveBalance->carried_over_days }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Hak Cuti Tahun Ini</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $annualLeaveBalance->total_days }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Cuti Telah Diambil</p>
                            <p class="text-2xl font-bold text-red-600">{{ $annualLeaveBalance->days_taken }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Sisa Cuti</p>
                            <p class="text-2xl font-bold text-green-600">{{ $annualLeaveBalance->carried_over_days + $annualLeaveBalance->total_days - $annualLeaveBalance->days_taken }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters for My Requests -->
                <form action="{{ route('leaves.index') }}" method="GET" class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="my_leave_type" class="block text-sm font-medium text-gray-700">Jenis Cuti</label>
                            <select name="my_leave_type" id="my_leave_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Semua Jenis</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('my_leave_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="my_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="my_status" id="my_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Semua Status</option>
                                @foreach($allStatuses as $status)
                                    <option value="{{ $status }}" {{ request('my_status') == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Filter Riwayat</button>
                            <a href="{{ route('leaves.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Cuti</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penyetuju Berikutnya</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($myRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->leaveType->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->duration_days }} hari</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-status-badge :status="$request->status" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->approver->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('leaves.show', $request) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">Anda belum pernah mengajukan cuti.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
