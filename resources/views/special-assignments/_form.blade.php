@php
    $existingMembers = [];
    if (old('members')) {
        // If there's old input from a submission attempt, use that
        foreach (old('members') as $index => $memberData) {
            $existingMembers[] = [
                'user_id' => (int) $memberData['user_id'],
                'role_in_sk' => $memberData['role_in_sk']
            ];
        }
    } elseif (isset($assignment) && $assignment->exists && $assignment->members) {
        // If it's an existing assignment being edited
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
            create_sk: {{ old('create_sk') ? 'true' : 'false' }}, // Inisialisasi dari old input
            init() {
                // Ensure initial Alpine.js data matches Laravel's old() values for select elements
                this.members = this.members.map(member => ({
                    user_id: parseInt(member.user_id), // Ensure user_id is a number
                    role_in_sk: member.role_in_sk
                }));
            },
            addMember() {
                this.members.push({ user_id: '', role_in_sk: 'Anggota' });
            },
            removeMember(index) {
                this.members.splice(index, 1);
            }
        }
    }
</script>

<div x-data="assignmentForm()">
    @if ($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-md" role="alert"> {{-- Menambahkan rounded-lg dan shadow-md --}}
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} error. Silakan periksa di bawah.</h3>
                    <ul class="mt-1.5 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-6"> {{-- Mengubah space-y-4 menjadi space-y-6 untuk konsistensi --}}

        @if(session('surat_id'))
            <input type="hidden" name="surat_id" value="{{ session('surat_id') }}">
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                <p><i class="fas fa-info-circle mr-2"></i>Anda sedang membuat penugasan khusus dari surat yang sudah ada. Nomor SK akan terisi otomatis.</p>
            </div>
        @endif

        <div>
            <label for="title" class="block font-semibold text-sm text-gray-700 mb-1">Judul / Uraian Penugasan <span class="text-red-600">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title', $assignment->title ?? '') }}" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('title') border-red-500 @enderror" required>
            @error('title') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        @if(!session('surat_id'))
            <div>
                <label for="sk_number" class="block font-semibold text-sm text-gray-700 mb-1">Nomor SK (Opsional)</label>
                <input type="text" name="sk_number" id="sk_number" value="{{ old('sk_number', $assignment->sk_number ?? '') }}" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('sk_number') border-red-500 @enderror">
                @error('sk_number') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- Mengubah gap-4 menjadi gap-6 --}}
            <div>
                <label for="start_date" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Mulai <span class="text-red-600">*</span></label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', optional($assignment->start_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('start_date') border-red-500 @enderror" required>
                @error('start_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="end_date" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Selesai (Opsional)</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', optional($assignment->end_date)->format('Y-m-d')) }}" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150 @error('end_date') border-red-500 @enderror">
                @error('end_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <div>
            <label for="status" class="block font-semibold text-sm text-gray-700 mb-1">Status</label>
            <select name="status" id="status" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
                <option value="AKTIF" @selected(old('status', $assignment->status ?? 'AKTIF') == 'AKTIF')>Aktif</option>
                <option value="SELESAI" @selected(old('status', $assignment->status ?? '') == 'SELESAI')>Selesai</option>
            </select>
        </div>

        <div>
            <label for="description" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi (Opsional)</label>
            <textarea name="description" id="description" rows="4" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">{{ old('description', $assignment->description ?? '') }}</textarea>
        </div>

        <div class="border-t border-gray-200 pt-6"> {{-- Menambahkan border-gray-200 --}}
            <label for="file_upload" class="block font-semibold text-sm text-gray-700 mb-1">Unggah File SK (PDF, JPG, PNG - Opsional, Max: 2MB)</label>
            <input type="file" name="file_upload" id="file_upload" class="block w-full mt-1 text-sm text-gray-500 
                file:mr-4 file:py-2 file:px-4 
                file:rounded-full file:border-0 
                file:text-sm file:font-semibold 
                file:bg-blue-50 file:text-blue-700 
                hover:file:bg-blue-100 hover:file:shadow-sm
                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150"> {{-- Menambahkan shadow dan fokus pada file input --}}
            @error('file_upload') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror

            @if ($assignment->exists && $assignment->file_path)
                <div class="mt-2 text-sm text-gray-600 flex items-center">
                    <i class="fas fa-paperclip mr-2 text-gray-400"></i> File saat ini: 
                    <a href="{{ asset('storage/' . $assignment->file_path) }}" target="_blank" class="text-blue-600 hover:underline ml-1 flex items-center">
                        {{ basename($assignment->file_path) }} <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                    </a>
                </div>
            @endif
        </div>
        
        {{-- MODIFIKASI: Tampilkan bagian ini hanya untuk Manajer --}}
        @if(auth()->user()->canManageUsers())
            <div class="border-t border-gray-200 pt-6"> {{-- Menambahkan border-gray-200 --}}
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-users-cog mr-2 text-purple-600"></i> Anggota Penugasan <span class="text-red-600 text-sm ml-2">*</span></h3>
                <div class="space-y-4"> {{-- Mengubah space-y-3 menjadi space-y-4 --}}
                    <template x-for="(member, index) in members" :key="index">
                        <div class="flex flex-wrap items-end gap-3 border border-gray-200 p-4 rounded-lg bg-gray-50 shadow-sm"> {{-- Menambahkan flex-wrap, gap, rounded-lg, bg-gray-50, shadow-sm --}}
                            <div class="flex-grow">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Personil</label> {{-- Menambahkan block, font-semibold, mb-1 --}}
                                <select :name="`members[${index}][user_id]`" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" x-model.number="member.user_id"> {{-- Rounded-lg, fokus, dan transisi --}}
                                    <option value="">-- Pilih Personil --</option>
                                    @foreach($assignableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-2/5"> {{-- Mengatur lebar responsif --}}
                                 <label class="block text-xs font-semibold text-gray-600 mb-1">Peran dalam SK</label> {{-- Menambahkan block, font-semibold, mb-1 --}}
                                <input type="text" :name="`members[${index}][role_in_sk]`" x-model="member.role_in_sk" placeholder="Contoh: Ketua, Anggota" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"> {{-- Rounded-lg, fokus, dan transisi --}}
                            </div>
                            <button type="button" @click="removeMember(index)" class="flex-shrink-0 text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors duration-200 self-center"> {{-- Menambahkan flex-shrink-0, rounded-full, hover, transisi --}}
                                <i class="fas fa-times"></i> {{-- Menggunakan icon Font Awesome --}}
                            </button>
                        </div>
                    </template>
                     @error('members') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                </div>
                <button type="button" @click="addMember()" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg"> {{-- Menambahkan shadow dan efek hover --}}
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Anggota
                </button>
            </div>
        @else
            {{-- Untuk Staf, cukup tampilkan teks informasi --}}
            <div class="border-t border-gray-200 pt-6"> {{-- Menambahkan border-gray-200 --}}
                <h3 class="text-base font-semibold text-gray-700 mb-2 flex items-center"><i class="fas fa-info-circle mr-2 text-blue-500"></i> Informasi Penugasan Anggota</h3>
                <p class="mt-1 text-sm text-gray-600 bg-gray-100 p-3 rounded-lg shadow-sm">Penugasan ini akan secara otomatis ditetapkan untuk Anda sebagai **Pelaksana**.</p> {{-- Menambahkan rounded-lg dan shadow-sm --}}
            </div>
        @endif
        {{-- AKHIR MODIFIKASI --}}
    </div>

    {{-- Fieldset for the digital archiving option --}}
    <fieldset class="border p-4 rounded-md">
        <legend class="text-lg font-semibold px-2">Pengarsipan</legend>
        <div class="p-4">
            <label for="berkas_id" class="block font-semibold text-sm text-gray-700 mb-1">
                <i class="fas fa-archive mr-2 text-gray-500"></i> Simpan ke Arsip Digital (Opsional)
            </label>
            <select name="berkas_id" id="berkas_id" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                <option value="">-- Jangan Arsipkan --</option>
                @isset($berkasList)
                    @foreach($berkasList as $berkas)
                        <option value="{{ $berkas->id }}" @selected(old('berkas_id') == $berkas->id)>
                            {{ $berkas->name }}
                        </option>
                    @endforeach
                @endisset
            </select>
            <p class="text-xs text-gray-500 mt-1">Pilih folder arsip digital untuk menyimpan SK ini secara otomatis.</p>
        </div>
    </fieldset>

    {{-- Bagian Tombol Simpan/Batal --}}
    <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200"> {{-- Menambahkan border-gray-200 dan meningkatkan pt --}}
        <a href="{{ route('special-assignments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium mr-6 transition-colors duration-200">Batal</a>
        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:scale-105">
            <i class="fas fa-save mr-2"></i> {{ $assignment->exists ? 'Update SK' : 'Simpan SK' }}
        </button>
    </div>
</div>