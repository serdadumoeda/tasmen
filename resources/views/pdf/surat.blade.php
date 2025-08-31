<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat: {{ $surat->perihal }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        .container {
            padding: 50px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid black;
            padding-bottom: 10px;
        }
        .content {
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-section {
            width: 300px;
            float: right;
            text-align: center;
        }
        .qr-code {
            float: left;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="kop-surat">
            {{-- Kop surat bisa ditambahkan di sini jika ada --}}
            <h3>KEMENTERIAN KETENAGAKERJAAN</h3>
            <h4>REPUBLIK INDONESIA</h4>
        </div>

        <div class="content">
            {!! $surat->konten !!}
        </div>

        <div class="footer clearfix">
            <div class="qr-code">
                {{-- QR code will be injected here as an image --}}
                <img src="data:image/svg+xml;base64, {!! $qrCode !!}" alt="QR Code">
            </div>
            <div class="signature-section">
                <p>Hormat kami,</p>
                @if ($signatureImagePath)
                    {{-- Signature image will be injected here --}}
                    <img src="{{ $signatureImagePath }}" alt="Tanda Tangan" style="height: 80px; width: auto;">
                @else
                    <div style="height: 80px;"></div>
                @endif
                <p><strong>{{ $surat->penyetuju->name ?? '' }}</strong></p>
                <p>{{ $surat->penyetuju->role ?? '' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
