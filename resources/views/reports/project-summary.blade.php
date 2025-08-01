<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Proyek: {{ $project->name }}</title>
    {{-- Menggunakan Font Awesome untuk ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #1a202c; /* gray-900 */
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #6b7280; /* gray-500 */
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #374151; /* gray-700 */
            border-bottom: 2px solid #e2e8f0; /* gray-200 */
            padding-bottom: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 8px;
            color: #4f46e5; /* indigo-600 */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-radius: 8px; /* rounded-lg */
            overflow: hidden; /* Untuk rounded corners pada tabel */
        }
        th, td {
            border: 1px solid #e2e8f0; /* gray-200 */
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6; /* gray-100 */
            font-weight: bold;
            color: #4b5563; /* gray-600 */
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            background-color: #ffffff;
            color: #374151; /* gray-700 */
            font-size: 12px;
        }
        .stats-table td {
            font-size: 14px;
            text-align: center;
            font-weight: bold;
            background-color: #f9fafb; /* gray-50 */
        }
        .label {
            color: #6b7280; /* gray-500 */
            font-weight: normal;
        }
        .value {
            font-weight: bold;
            color: #1f2937; /* gray-800 */
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px; /* rounded-full */
            font-size: 10px;
            font-weight: bold;
            text-transform: capitalize;
            text-align: center;
        }
        .status-pending { background-color: #fef08a; color: #92400e; } /* yellow-200, yellow-800 */
        .status-in_progress { background-color: #fed7aa; color: #9a3412; } /* orange-200, orange-800 */
        .status-for_review { background-color: #bfdbfe; color: #1e40af; } /* blue-200, blue-800 */
        .status-completed { background-color: #a7f3d0; color: #065f46; } /* green-200, green-800 */

        .member-list li {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            color: #374151; /* gray-700 */
        }
        .member-list li i {
            margin-right: 6px;
            color: #9ca3af; /* gray-400 */
        }
        .project-description {
            color: #4b5563; /* gray-600 */
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .info-row i {
            margin-right: 8px;
            color: #9ca3af; /* gray-400 */
        }
        .info-row .label-text {
            font-weight: normal;
            color: #6b7280;
            width: 150px; /* Lebar tetap untuk label */
            flex-shrink: 0;
        }
        .info-row .value-text {
            font-weight: bold;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Status Proyek</h1>
            <p>Tanggal Dibuat: {{ now()->format('d F Y, H:i') }}</p>
        </div>

        <div class="section">
            <div class="section-title"><i class="fas fa-folder-open"></i> Informasi Proyek</div>
            <h2 style="font-size: 20px; color: #1f2937; margin-bottom: 10px;">{{ $project->name }}</h2>
            <p class="project-description">{{ $project->description }}</p>
            
            <table>
                <tr>
                    <td class="label" width="30%"><i class="fas fa-user-tie"></i> Ketua Tim</td>
                    <td class="value">{{ $project->leader->name }}</td>
                </tr>
                <tr>
                    <td class="label"><i class="fas fa-calendar-alt"></i> Tanggal Mulai</td>
                    <td class="value">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label"><i class="fas fa-calendar-check"></i> Tanggal Selesai</td>
                    <td class="value">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label"><i class="fas fa-hourglass-half"></i> Waktu Estimasi (Total)</td>
                    <td class="value">{{ $project->tasks->sum('estimated_hours') }} jam</td>
                </tr>
                 <tr>
                    <td class="label"><i class="fas fa-stopwatch"></i> Waktu Tercatat (Total)</td>
                    <td class="value">{{ $totalLoggedTime }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title"><i class="fas fa-chart-pie"></i> Ringkasan Statistik Tugas</div>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-list-check"></i> Total Tugas</th>
                        <th><i class="fas fa-hourglass-start"></i> Pending</th>
                        <th><i class="fas fa-person-digging"></i> Dikerjakan</th>
                        <th><i class="fas fa-check-double"></i> Selesai</th>
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
            <div class="section-title"><i class="fas fa-tasks"></i> Rincian Tugas</div>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Judul Tugas</th>
                        <th>Ditugaskan Kepada</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->tasks as $task)
                        <tr class="status-{{ $task->status }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $task->title }}</td>
                            <td>
                                @forelse($task->assignees as $assignee)
                                    {{ $assignee->name }}{{ !$loop->last ? ', ' : '' }}
                                @empty
                                    N/A
                                @endforelse
                            </td>
                            <td>{{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</td>
                            <td>
                                <span class="status-badge status-{{ $task->status }}">
                                    {{ str_replace('_', ' ', Str::title($task->status)) }}
                                </span>
                            </td>
                            <td>{{ $task->progress }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title"><i class="fas fa-users-line"></i> Anggota Tim</div>
             <ul class="member-list">
                @forelse($project->members as $member)
                    <li><i class="fas fa-user-tag"></i> {{ $member->name }} ({{ $member->role }})</li>
                @empty
                    <li>Tidak ada anggota tim.</li>
                @endforelse
            </ul>
        </div>
    </div>
</body>
</html>