<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keputusan Penugasan Khusus</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h3 {
            margin: 0;
            text-decoration: underline;
        }
        .header p {
            margin: 0;
        }
        .content {
            margin-top: 20px;
        }
        .content p {
            text-align: justify;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .signature {
            margin-top: 60px;
            text-align: right;
        }
        .signature .name {
            margin-top: 70px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h3>SURAT KEPUTUSAN</h3>
        <p>Nomor: {{ $assignment->sk_number ?? '________________' }}</p>
    </div>

    <div class="content">
        <h4>TENTANG</h4>
        <h4>PENUGASAN KHUSUS: {{ strtoupper($assignment->title) }}</h4>

        <p>Menimbang dan seterusnya...</p>

        <h4>MEMUTUSKAN:</h4>
        <p><strong>Pertama:</strong> Menugaskan kepada nama-nama yang tercantum di bawah ini untuk melaksanakan kegiatan "{{ $assignment->title }}" yang akan diselenggarakan pada tanggal {{ \Carbon\Carbon::parse($assignment->start_date)->isoFormat('D MMMM Y') }} sampai dengan {{ \Carbon\Carbon::parse($assignment->end_date)->isoFormat('D MMMM Y') }}.</p>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Peran dalam SK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignment->members as $index => $member)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->pivot->role_in_sk }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Kedua:</strong> Segala biaya yang timbul akibat dikeluarkannya Surat Keputusan ini dibebankan pada anggaran yang berlaku.</p>
        <p><strong>Ketiga:</strong> Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan akan diperbaiki sebagaimana mestinya jika di kemudian hari terdapat kekeliruan.</p>
    </div>

    <div class="signature">
        <p>Ditetapkan di: Jakarta</p>
        <p>Pada tanggal: {{ now()->isoFormat('D MMMM Y') }}</p>
        <p>Yang Menetapkan,</p>
        <p class="name"><strong>(________________)</strong></p>
    </div>

</body>
</html>
