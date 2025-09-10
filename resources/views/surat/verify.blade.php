<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 mb-10 max-w-3xl">
        <div class="bg-white shadow-2xl rounded-lg p-8">
            <div class="flex items-center justify-center text-center border-b pb-6">
                <i class="fas fa-check-circle text-green-500 fa-4x"></i>
                <div class="ml-4">
                    <h1 class="text-3xl font-bold text-gray-800">Dokumen Terverifikasi</h1>
                    <p class="text-gray-500">Dokumen ini adalah dokumen asli yang diterbitkan melalui sistem.</p>
                </div>
            </div>
            <div class="mt-6 space-y-4">
                <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                    <span class="text-sm font-semibold text-gray-500">Perihal Surat</span>
                    <span class="text-lg text-gray-900 font-bold">{{ $surat->perihal }}</span>
                </div>
                <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                    <span class="text-sm font-semibold text-gray-500">Nomor Surat</span>
                    <span class="text-lg text-gray-900 font-mono">{{ $surat->nomor_surat ?? 'Belum Dinomori' }}</span>
                </div>
                <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                    <span class="text-sm font-semibold text-gray-500">Tanggal Surat</span>
                    <span class="text-lg text-gray-900">{{ $surat->tanggal_surat->format('d F Y') }}</span>
                </div>
                <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                    <span class="text-sm font-semibold text-gray-500">Status</span>
                    <span class="text-lg text-white font-bold px-3 py-1 bg-green-600 rounded-full self-start mt-1">{{ ucfirst($surat->status) }}</span>
                </div>
                <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                    <span class="text-sm font-semibold text-gray-500">Disetujui oleh</span>
                    <span class="text-lg text-gray-900 font-bold">{{ $surat->penyetuju->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="mt-8 text-center text-xs text-gray-400">
                <p>Diverifikasi pada: {{ now()->format('d M Y H:i:s') }}</p>
                <p class="mt-2">Ini adalah halaman verifikasi otomatis. Tidak diperlukan balasan.</p>
            </div>
        </div>
    </div>
</body>
</html>
