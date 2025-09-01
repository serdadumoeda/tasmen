<?php

namespace App\Services\Tte;

use App\Models\Surat;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * A dummy TTE provider for local development and testing.
 * It does not connect to any external API. Instead, it generates a regular PDF
 * and simulates a "signed" state, for example by adding a watermark.
 */
class LocalTteProvider implements TteProvider
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function sign(Surat $surat, array $pdfData): string
    {
        // In a real provider, this is where you would make the API call.
        // For the local provider, we just generate the PDF as is.
        // The logic here is moved from the SuratKeluarController.

        // 1. Generate the PDF from the view.
        $pdf = Pdf::loadView('pdf.surat', $pdfData);

        // Optional: Add a watermark to indicate this is a locally signed dev document.
        $watermarkText = $this->config['watermark_text'] ?? 'DRAFT';
        $pdf->output(); // Necessary to get page count and dimensions
        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->page_text(
            10,
            $canvas->get_height() - 20,
            $watermarkText . " - Signed on: " . now()->toDateTimeString(),
            null,
            8,
            [0.5, 0.5, 0.5]
        );

        // 2. Define a filename and path in storage.
        $filename = 'surat-local-' . $surat->id . '-' . time() . '.pdf';
        $path = 'surat-final/' . $filename;

        // 3. Save the PDF to the public storage disk.
        Storage::disk('public')->put($path, $pdf->output());

        // 4. Return the storage path.
        return $path;
    }
}
