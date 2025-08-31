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
            position: relative; /* Needed for absolute positioning of logo */
            min-height: 100px; /* Give some space for the logo */
        }
        .logo {
            position: absolute;
            top: 0;
            left: 0;
            height: 90px; /* Adjust as needed */
        }
        .kop-text {
            /* No specific styles needed, but can be used for fine-tuning */
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
            @if(isset($settings['logo_path']) && $settings['logo_path'])
                <img src="{{ storage_path('app/public/' . $settings['logo_path']) }}" class="logo" alt="Logo">
            @endif
            <div class="kop-text">
                <h3>{{ $settings['letterhead_line_1'] ?? '' }}</h3>
                <h4>{{ $settings['letterhead_line_2'] ?? '' }}</h4>
                <p>{{ $settings['letterhead_line_3'] ?? '' }}</p>
                <p>{{ $settings['letterhead_line_4'] ?? '' }}</p>
                <p>{{ $settings['letterhead_line_5'] ?? '' }}</p>
            </div>
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
                <p>{{ $settings['signer_block_line_1'] ?? 'Hormat kami,' }}</p>
                <p>{{ $settings['signer_block_line_2'] ?? '' }}</p>
                @if ($signatureImagePath)
                    {{-- Signature image will be injected here --}}
                    <img src="{{ $signatureImagePath }}" alt="Tanda Tangan" style="height: 80px; width: auto;">
                @else
                    <div style="height: 80px;"></div>
                @endif
                <p><strong>{{ $surat->penyetuju->name ?? '' }}</strong></p>
                <p>{{ $settings['signer_block_line_3'] ?? '' }}</p>
                <p>{{ $settings['signer_block_line_4'] ?? '' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
