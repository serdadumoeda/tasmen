<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Template Notifikasi: ') }} {{ $notificationTemplate->key }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('admin.notification-templates.update', $notificationTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subjek</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject', $notificationTemplate->subject) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('subject')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700">Isi (Body)</label>
                            <textarea name="body" id="body" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" required>{{ old('body', $notificationTemplate->body) }}</textarea>
                            @error('body')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi & Placeholder</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('description', $notificationTemplate->description) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Jelaskan placeholder yang tersedia untuk template ini (contoh: {{user_name}}, {{task_title}}).</p>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-6 border-t">
                        <a href="{{ route('admin.notification-templates.index') }}" class="mr-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
