<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Template Notifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Daftar Template</h3>
                </div>

                @if (session('success'))
                    <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 border border-green-300 rounded-md p-3">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subjek</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($templates as $template)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $template->key }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $template->subject }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.notification-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Tidak ada data template notifikasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
