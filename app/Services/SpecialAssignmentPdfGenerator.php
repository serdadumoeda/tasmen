<?php

namespace App\Services;

use App\Models\SpecialAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpecialAssignmentPdfGenerator
{
    /**
     * Generate a PDF for a given Special Assignment and save it to storage.
     *
     * @param SpecialAssignment $assignment The special assignment instance.
     * @return string The path to the saved PDF file.
     */
    public function generate(SpecialAssignment $assignment): string
    {
        // Ensure the assignment members are loaded
        $assignment->load('members');

        // Load the view and pass the data
        $pdf = Pdf::loadView('pdf.special_assignment', ['assignment' => $assignment]);

        // Generate a unique filename
        $filename = 'SK_Penugasan_' . Str::slug($assignment->title) . '_' . $assignment->id . '.pdf';

        // Define the storage path
        $directory = 'sk_files';
        $path = "{$directory}/{$filename}";

        // Ensure the directory exists
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        // Save the PDF to the local storage disk
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
