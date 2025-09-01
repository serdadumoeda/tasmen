<?php

namespace App\Services\Tte;

use App\Models\Surat;

/**
 * Interface for a Tanda Tangan Elektronik (TTE) provider.
 */
interface TteProvider
{
    /**
     * Sign a given Surat model's content and return the path to the signed PDF.
     *
     * This method should orchestrate the entire signing process:
     * 1. Generate the initial PDF from the letter's data.
     * 2. Send the PDF to the TTE provider's API for signing.
     * 3. Receive the signed PDF.
     * 4. Store the signed PDF in a permanent location.
     * 5. Return the storage path of the final signed PDF.
     *
     * @param Surat $surat The letter to be signed.
     * @param array $pdfData Additional data needed to render the PDF (e.g., QR code, settings).
     * @return string The storage path to the final, signed PDF file.
     * @throws \Exception If the signing process fails.
     */
    public function sign(Surat $surat, array $pdfData): string;
}
