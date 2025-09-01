<?php

namespace App\Services\Tte;

use Illuminate\Support\Manager;

class TteManager extends Manager implements TteProvider
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('services.tte.driver', 'local');
    }

    /**
     * Create an instance of the local TTE driver.
     *
     * @return \App\Services\Tte\LocalTteProvider
     */
    protected function createLocalDriver()
    {
        $config = $this->config->get('services.tte.local', []);
        return new LocalTteProvider($config);
    }

    /**
     * Create an instance of the BSrE TTE driver.
     *
     * This is a placeholder. The user will need to implement the actual
     * BsreTteProvider class and its logic.
     *
     * @return \App\Services\Tte\BsreTteProvider
     */
    protected function createBsreDriver()
    {
        // The user will need to create this class: app/Services/Tte/BsreTteProvider.php
        if (!class_exists(BsreTteProvider::class)) {
            throw new \InvalidArgumentException("BSrE TTE provider class not found. Please create it.");
        }

        $config = $this->config->get('services.tte.bsre', []);
        return new BsreTteProvider($config);
    }

    /**
     * Pass calls to the driver.
     */
    public function sign(Surat $surat, array $pdfData): string
    {
        return $this->driver()->sign($surat, $pdfData);
    }
}
