<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Proyek: {{ $project->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #777;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        .stats-table td {
            font-size: 14px;
            text-align: center;
            font-weight: bold;
        }
        .label {
            color: #777;
        }
        .value {
            font-weight: bold;
        }
        .status-pending { background-color: #fef9c3; }
        .status-in_progress { background-color: #ffedd5; }
        .status-completed { background-color: #dcfce7; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Status Proyek</h1>
            <p>Tanggal Dibuat: {{ now()->format('d F Y, H:i') }}</p>
        </div>

        <div class="section">
            <div class="section-title">{{ $project->name }}</div>
            <p>{{ $project->description }}</p>
            <table>
                <tr>
                    <td class="label" width="30%">Ketua Tim</td>
                    <td class="value">{{ $project->leader->name }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Estimasi (Total)</td>
                    <td class="value">{{ $project->tasks->sum('estimated_hours') }} jam</td>
                </tr>
                 <tr>
                    <td class="label">Waktu Tercatat (Total)</td>
                    <td class="value">{{ $totalLoggedTime }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Ringkasan Statistik</div>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Total Tugas</th>
                        <th>Pending</th>
                        <th>Dikerjakan</th>
                        <th>Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $stats['total'] }}</td>
                        <td>{{ $stats['pending'] }}</td>
                        <td>{{ $stats['in_progress'] }}</td>
                        <td>{{ $stats['completed'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Rincian Tugas</div>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Judul Tugas</th>
                        <th>Ditugaskan Kepada</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->tasks as $task)
                        <tr class="status-{{ $task->status }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->assignedTo->name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</td>
                            <td>{{ str_replace('_', ' ', Str::title($task->status)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Anggota Tim</div>
             <ul>
                @foreach($project->members as $member)
                    <li>{{ $member->name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</body>
</html>