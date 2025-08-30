<?php

namespace App\Http\Controllers;

use App\Models\LampiranSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LampiranController extends Controller
{
    public function show(LampiranSurat $lampiranSurat)
    {
        $this->authorize('view', $lampiranSurat);

        if (!Storage::disk('public')->exists($lampiranSurat->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->response($lampiranSurat->path_file);
    }
}
