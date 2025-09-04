<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kinerja Harian</title>
    <style>
        /* Tambahkan CSS untuk PDF di sini, misalnya ukuran kertas, font, dll. */
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Laporan Kinerja Harian</h1>
    <p><strong>Nama:</strong> {{ $user->name }}</p>
    <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal Selesai</th>
                <th>Uraian Tugas</th>
                <th>Kegiatan/Proyek</th>
                <th>Output</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $key => $task)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $task->updated_at->format('d M Y') }}</td>
                <td>{{ $task->name }}</td>
                <td>{{ $task->project->name ?? 'Tugas Harian' }}</td>
                <td>Tercapai</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
