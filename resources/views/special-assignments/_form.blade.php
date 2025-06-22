@php
    $existingMembers = [];
    if (old('members')) {
        $existingMembers = old('members');
    } elseif (isset($assignment) && $assignment->exists && $assignment->members) {
        $existingMembers = $assignment->members->map(function($member) {
            return [
                'user_id' => $member->id,
                'role_in_sk' => $member->pivot->role_in_sk
            ];
        })->toArray();
    }
@endphp

<script>
    function assignmentForm() {
        return {
            members: @json($existingMembers),
            addMember() {
                this.members.push({ user_id: '', role_in_sk: '' });
            },
            removeMember(index) {
                this.members.splice(index, 1);
            }
        }
    }
</script>

<div x-data="assignmentForm()">
    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} error. Silakan periksa di bawah.</h3>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-6">
        <div>
            <label for="title" class="block font-medium text-sm text-gray-700">Judul / Uraian Penugasan <span class="text-red-600">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title', $assignment->title ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('title') border-red-500 @enderror" required>
            @error('title') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="sk_number" class="block font-medium text-sm text-gray-700">Nomor SK (Opsional)</label>
            <input type="text" name="sk_number" id="sk_number" value="{{ old('sk_number', $assignment->sk_number ?? '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('sk_number') border-red-500 @enderror">
            @error('sk_number') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block font-medium text-sm text-gray-700">Tanggal Mulai <span class="text-red-600">*</span></label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', optional($assignment->start_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('start_date') border-red-500 @enderror" required>
                @error('start_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="end_date" class="block font-medium text-sm text-gray-700">Tanggal Selesai (Opsional)</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', optional($assignment->end_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 @error('end_date') border-red-500 @enderror">
                @error('end_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <div>
            <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
            <select name="status" id="status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                <option value="AKTIF" @selected(old('status', $assignment->status ?? 'AKTIF') == 'AKTIF')>Aktif</option>
                <option value="SELESAI" @selected(old('status', $assignment->status ?? '') == 'SELESAI')>Selesai</option>
            </select>
        </div>

        <div>
            <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi (Opsional)</label>
            <textarea name="description" id="description" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description', $assignment->description ?? '') }}</textarea>
        </div>
        
        <div class="border-t pt-6">
            <label for="file_upload" class="block font-medium text-sm text-gray-700">Unggah File SK (PDF, JPG, PNG - Opsional, Max: 2MB)</label>
            <input type="file" name="file_upload" id="file_upload" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @error('file_upload') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror

            @if ($assignment->exists && $assignment->file_path)
                <div class="mt-2 text-sm text-gray-600">
                    File saat ini: 
                    <a href="{{ asset('storage/' . $assignment->file_path) }}" target="_blank" class="text-blue-600 hover:underline">
                        {{ basename($assignment->file_path) }}
                    </a>
                </div>
            @endif
        </div>
        
        {{-- MODIFIKASI: Tampilkan bagian ini hanya untuk Manajer --}}
        @if(auth()->user()->canManageUsers())
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium mb-2">Anggota Penugasan <span class="text-red-600">*</span></h3>
                <div class="space-y-3">
                    <template x-for="(member, index) in members" :key="index">
                        <div class="flex items-center space-x-2 border p-2 rounded-md bg-gray-50">
                            <div class="flex-grow">
                                <label class="text-xs text-gray-600">Personil</label>
                                <select :name="`members[${index}][user_id]`" class="w-full rounded-md border-gray-300 text-sm" x-model.number="member.user_id">
                                    <option value="">-- Pilih Personil --</option>
                                    @foreach($assignableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-2/5">
                                 <label class="text-xs text-gray-600">Peran dalam SK</label>
                                <input type="text" :name="`members[${index}][role_in_sk]`" x-model="member.role_in_sk" placeholder="Contoh: Ketua, Anggota" class="w-full rounded-md border-gray-300 text-sm">
                            </div>
                            <button type="button" @click="removeMember(index)" class="text-red-500 hover:text-red-700 p-2 mt-5 self-start">&times;</button>
                        </div>
                    </template>
                     @error('members') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                </div>
                <button type="button" @click="addMember()" class="mt-3 text-sm font-semibold text-blue-600 hover:text-blue-800">+ Tambah Anggota</button>
            </div>
        @else
            {{-- Untuk Staf, cukup tampilkan teks informasi --}}
            <div class="border-t pt-4">
                <h3 class="text-sm font-medium text-gray-700">Anggota Penugasan</h3>
                <p class="mt-1 text-sm text-gray-600 bg-gray-100 p-3 rounded-md">Penugasan ini akan secara otomatis ditetapkan untuk Anda sebagai **Pelaksana**.</p>
            </div>
        @endif
        {{-- AKHIR MODIFIKASI --}}
    </div>

    <div class="flex items-center justify-end mt-8 pt-4 border-t">
        <a href="{{ route('special-assignments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            {{ $assignment->exists ? 'Update SK' : 'Simpan SK' }}
        </button>
    </div>
</div>