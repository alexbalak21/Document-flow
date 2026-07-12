<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    /**
     * Generate and download a PDF for a saved document.
     * Uses the stored html_snapshot so no re-render needed.
     */
    public function download(Document $document)
    {
        $filename = $this->buildFilename($document);
        $html     = $document->html_snapshot;

        if (! $html) {
            return back()->with('error', 'No HTML snapshot found for this document.');
        }

        try {
            $pdf = $this->buildBrowsershot($html)->pdf();
        } catch (\Throwable $e) {
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
            $pdf = $this->buildBrowsershot($html)->pdf();
        } catch (\Throwable $e) {
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function buildBrowsershot(string $html): Browsershot
    {
        return Browsershot::html($html)
            ->format('A4')
            ->margins(0, 0, 0, 0)   // document templates handle their own padding
            ->showBackground()       // renders CSS backgrounds, colors, borders
            ->emulateMedia('print')  // uses @media print CSS rules
            ->waitUntilNetworkIdle() // waits for embedded images / fonts to load
            ->timeout(60)
            ->noSandbox();           // required on most Linux servers
    }

    private function buildFilename(Document $document): string
    {
        $ref  = $document->reference ?? $document->title ?? 'document-' . $document->id;
        $slug = Str::slug($ref);
        return $slug . '.pdf';
    }
}
