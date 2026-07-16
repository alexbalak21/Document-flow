<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PdfController extends Controller
{
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
            if (config('app.debug')) throw $e;
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

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
            if (config('app.debug')) throw $e;
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
        $serviceUrl = config('services.pdf.url', 'http://127.0.0.1:5001');

        $response = Http::timeout(30)
            ->withBody($html, 'text/html; charset=UTF-8')
            ->post($serviceUrl . '/pdf');

        if ($response->failed()) {
            $error = $response->json('error') ?? $response->body();
            throw new \RuntimeException('PDF service error: ' . $error);
        }

        return $response->body();
    }

    private function buildFilename(Document $document): string
    {
        $ref  = $document->reference ?? $document->title ?? 'document-' . $document->id;
        $slug = Str::slug($ref);
        return $slug . '.pdf';
    }
}