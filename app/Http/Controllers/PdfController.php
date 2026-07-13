<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class PdfController extends Controller
{
    /**
     * Generate and download a PDF for a saved document.
     */
    public function download(Document $document)
    {
        $filename = $this->buildFilename($document);
        $html     = $document->html_snapshot;

        if (! $html) {
            return back()->with('error', 'No HTML snapshot found for this document.');
        }

        try {
            $pdf = $this->buildPdf($html);
        } catch (\Throwable $e) {
            // In development, show the real error so we can debug it
            if (config('app.debug')) {
                throw $e;
            }
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    /**
     * Generate and inline-display (open in browser tab) a PDF.
     */
    public function preview(Document $document)
    {
        $filename = $this->buildFilename($document);
        $html     = $document->html_snapshot;

        if (! $html) {
            return back()->with('error', 'No HTML snapshot found for this document.');
        }

        try {
            $pdf = $this->buildPdf($html);
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                throw $e;
            }
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function buildPdf(string $html): string
    {
        // mPDF needs a writable temp directory — use Laravel's storage path
        $tmpDir = storage_path('app/mpdf-tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'                => 'utf-8',
            'format'              => 'A4',
            'margin_top'          => 0,
            'margin_bottom'       => 0,
            'margin_left'         => 0,
            'margin_right'        => 0,
            'setAutoTopMargin'    => false,
            'setAutoBottomMargin' => false,
            'tempDir'             => $tmpDir,
        ]);

        $mpdf->img_dpi = 200;
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S'); // 'S' = return as string
    }

    private function buildFilename(Document $document): string
    {
        $ref  = $document->reference ?? $document->title ?? 'document-' . $document->id;
        $slug = Str::slug($ref);
        return $slug . '.pdf';
    }
}