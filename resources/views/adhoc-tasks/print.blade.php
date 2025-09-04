<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja Harian - {{ $user->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        h1, h2 { text-align: center; }
        h1 { font-size: 1.5em; }
        h2 { font-size: 1.2em; font-weight: normal; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .no-print { display: none; }
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            .print-button { display: none; }
        }
        .print-button {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Kinerja Harian</h1>
        <h2>{{ $user->name }}</h2>
        <p><strong>Periode Laporan:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal Selesai</th>
                    <th>Judul Tugas</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $index => $task)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $task->updated_at->format('d M Y') }}</td>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">Tidak ada tugas yang diselesaikan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <button onclick="window.print()" class="print-button">Cetak</button>
    </div>
</body>
</html>
