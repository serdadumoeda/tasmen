<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log Aktivitas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pengguna
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Perubahan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Waktu
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->user->name ?? 'Sistem' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $descriptionParts = explode('_', $activity->description);
                                                $action = ucfirst($descriptionParts[0]);
                                                $subject = ucfirst($descriptionParts[1] ?? '');

                                                $translations = [
                                                    'Created' => 'Membuat',
                                                    'Updated' => 'Memperbarui',
                                                    'Deleted' => 'Menghapus',
                                                    'Approved' => 'Menyetujui',
                                                    'Rejected' => 'Menolak',
                                                    'Disposisi' => 'Disposisi',
                                                    'Leaverequest' => 'Permintaan Cuti',
                                                    'Suratkeluar' => 'Surat Keluar',
                                                ];

                                                $translatedAction = $translations[$action] ?? $action;
                                                $translatedSubject = $translations[$subject] ?? $subject;

                                                echo "$translatedAction $translatedSubject";
                                            @endphp
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if ($activity->before || $activity->after)
                                                <div class="flex space-x-4">
                                                    <div>
                                                        <strong class="font-semibold">Sebelum:</strong>
                                                        <pre class="text-xs bg-red-50 p-2 rounded">{{ json_encode($activity->before, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                    <div>
                                                        <strong class="font-semibold">Sesudah:</strong>
                                                        <pre class="text-xs bg-green-50 p-2 rounded">{{ json_encode($activity->after, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada aktivitas yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
