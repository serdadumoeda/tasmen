{{-- resources/views/projects/partials/persuratan.blade.php --}}
<h3 class="text-xl font-semibold mb-3">Dasar Surat Kegiatan</h3>

<p class="text-sm text-gray-600 mb-4">Berikut adalah daftar surat yang menjadi dasar pelaksanaan kegiatan ini. Anda dapat menambahkan atau mengubahnya melalui halaman "Edit Kegiatan".</p>

@can('create', App\Models\Surat::class)
  {{-- The feature to link mail to a project is not fully implemented. This now points to the general create page. --}}
  <a href="{{ route('surat.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
    <i class="fas fa-plus-circle mr-2"></i> Buat Surat Baru
  </a>
@endcan

<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Surat</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @forelse($suratList as $s)
          <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $s->nomor_surat ?? '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $s->perihal }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $s->tanggal_surat->format('d M Y') }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $s->status === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($s->status) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <a href="{{ route('surat.show', $s->id) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">Belum ada surat terkait dengan kegiatan ini.</td></tr>
        @endforelse
      </tbody>
    </table>
</div>
