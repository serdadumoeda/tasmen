<?php

namespace App\Services\Tte;

use App\Models\Surat;

/**
 * TTE provider for BSrE (Balai Sertifikasi Elektronik).
 *
 * This is a placeholder class. The user needs to implement the logic to
 * communicate with the BSrE API here.
 */
class BsreTteProvider implements TteProvider
{
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
        // ====================================================================
        //  USER ACTION REQUIRED
        // ====================================================================
        //
        // Implement the BSrE API communication logic here.
        //
        // 1. Get credentials from config:
        //    $apiUrl = $this->config['url'];
        //    $username = $this->config['username'];
        //    $password = $this->config['password'];
        //
        // 2. Generate the initial PDF (or use a pre-generated one).
        //    You can reuse the logic from LocalTteProvider for PDF generation.
        //
        // 3. Send the PDF to the BSrE signing endpoint using an HTTP client.
        //    - You might need to authenticate first.
        //    - The API might require the PDF to be base64 encoded.
        //
        // 4. Poll for status or handle callbacks if the API is asynchronous.
        //
        // 5. Download the signed PDF from BSrE.
        //
        // 6. Store the signed PDF in Laravel Storage.
        //    $path = 'surat-final/signed-bsre-' . $surat->id . '.pdf';
        //    Storage::disk('public')->put($path, $signedPdfContent);
        //
        // 7. Return the final storage path.
        //    return $path;
        //
        // ====================================================================

        throw new \LogicException("BSrE TTE provider has not been implemented yet.");
    }
}
