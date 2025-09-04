<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Alur Kerja: <span class="text-indigo-600">{{ $workflow->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Workflow Details --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="text-gray-600">{{ $workflow->description }}</p>
                </div>
            </div>

            {{-- Workflow Steps --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Langkah-langkah Persetujuan</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Langkah Ke-</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran Approver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Final?</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($workflow->steps as $step)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $step->step }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $step->approver_role }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($step->is_final_approval)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ya</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('admin.approval-workflows.steps.destroy', ['approvalWorkflow' => $workflow, 'step' => $step]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus langkah ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-gray-500">
                                            Belum ada langkah yang ditambahkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Add New Step Form --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Langkah Baru</h3>
                    <form action="{{ route('admin.approval-workflows.steps.store', $workflow) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label for="step" class="block text-sm font-medium text-gray-700">Langkah Ke-</label>
                                <input type="number" name="step" id="step" value="{{ ($workflow->steps->max('step') ?? 0) + 1 }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="approver_role" class="block text-sm font-medium text-gray-700">Peran Approver</label>
                                <select name="approver_role" id="approver_role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    @foreach($roles as $role)
                                        <option value="{{ $role['name'] }}">{{ $role['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center mt-6">
                                <label for="is_final_approval" class="flex items-center">
                                    <input type="checkbox" name="is_final_approval" id="is_final_approval" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    <span class="ml-2 text-sm text-gray-600">Jadikan Approval Final</span>
                                </label>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Tambah Langkah
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
