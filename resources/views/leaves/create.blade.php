<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajukan Permintaan Cuti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 bg-white border-b border-gray-200">
                    <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                        <p class="font-bold">Sisa Cuti Tahunan Anda:</p>
                        <p class="text-2xl">{{ $remainingDays }} Hari</p>
                    </div>

                    <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Leave Type -->
                        <div class="mb-4">
                            <label for="leave_type_id" class="block font-medium text-sm text-gray-700">{{ __('Jenis Cuti') }}</label>
                            <select name="leave_type_id" id="leave_type_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="start_date" class="block font-medium text-sm text-gray-700">{{ __('Tanggal Mulai') }}</label>
                                <input type="date" name="start_date" id="start_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            </div>
                            <div>
                                <label for="end_date" class="block font-medium text-sm text-gray-700">{{ __('Tanggal Selesai') }}</label>
                                <input type="date" name="end_date" id="end_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label for="reason" class="block font-medium text-sm text-gray-700">{{ __('Alasan Cuti') }}</label>
                            <textarea name="reason" id="reason" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required></textarea>
                        </div>

                        <!-- Address During Leave -->
                        <div class="mb-4">
                            <label for="address_during_leave" class="block font-medium text-sm text-gray-700">{{ __('Alamat Selama Cuti') }}</label>
                            <input type="text" name="address_during_leave" id="address_during_leave" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Contact During Leave -->
                        <div class="mb-4">
                            <label for="contact_during_leave" class="block font-medium text-sm text-gray-700">{{ __('Kontak Selama Cuti') }}</label>
                            <input type="text" name="contact_during_leave" id="contact_during_leave" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Attachment -->
                        <div class="mb-6">
                            <label for="attachment" class="block font-medium text-sm text-gray-700">{{ __('Lampiran (jika diperlukan)') }}</label>
                            <input type="file" name="attachment" id="attachment" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('leaves.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                {{ __('Ajukan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
