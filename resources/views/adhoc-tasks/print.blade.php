<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tugas Harian</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .header p {
            margin: 5px 0;
            font-size: 1em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .filters {
            margin-bottom: 20px;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .filters p {
            margin: 0 0 5px 0;
        }
        .filters span {
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        .print-button {
            display: block;
            width: 120px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Tugas Harian</h1>
            <p>Dicetak oleh: {{ $user->name }} pada {{ now()->format('d M Y H:i') }}</p>
        </div>

        <div class="filters">
            <p>Filter Aktif:</p>
            <ul>
                @if($filters['search'] ?? null)
                    <li>Kata Kunci: <span>{{ $filters['search'] }}</span></li>
                @endif
                @if($filters['task_status_id'] ?? null)
                    <li>Status: <span>{{ $statuses[$filters['task_status_id']]->label ?? 'N/A' }}</span></li>
                @endif
                @if($filters['priority_level_id'] ?? null)
                    <li>Prioritas: <span>{{ $priorities[$filters['priority_level_id']]->name ?? 'N/A' }}</span></li>
                @endif
                @if($filters['personnel_id'] ?? null)
                    <li>Personel: <span>{{ $personnel[$filters['personnel_id']]->name ?? 'N/A' }}</span></li>
                @endif
                @if(request('start_date') && request('end_date'))
                    <li>Rentang Deadline: <span>{{ \Carbon\Carbon::parse(request('start_date'))->format('d M Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d M Y') }}</span></li>
                @endif
                 @if(empty(array_filter($filters)) && !request('start_date'))
                    <li><span>Tidak ada filter aktif</span></li>
                @endif
            </ul>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th>Judul Tugas</th>
                    <th style="width: 20%;">Pelaksana</th>
                    <th style="width: 12%;">Deadline</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 12%;">Prioritas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $index => $task)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $task->title }}</td>
                        <td>
                            @foreach($task->assignees as $assignee)
                                {{ $assignee->name }}{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </td>
                        <td>{{ optional($task->deadline)->format('d M Y') ?? '-' }}</td>
                        <td>{{ $task->status->label ?? 'N/A' }}</td>
                        <td>{{ $task->priorityLevel->name ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">Tidak ada data yang cocok dengan filter yang diterapkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <a href="javascript:window.print()" class="print-button">Cetak Laporan</a>
    </div>
</body>
</html>
