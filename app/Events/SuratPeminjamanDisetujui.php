<?php

namespace App\Events;

use App\Models\Surat;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratPeminjamanDisetujui
{
    use Dispatchable, SerializesModels;

    public $surat;

    public function __construct(Surat $surat)
    {
        $this->surat = $surat;
    }
}
