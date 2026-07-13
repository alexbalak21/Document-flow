<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
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
            $pdf = $this->buildPdf($html);
        } catch (\Throwable $e) {
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return $pdf->download($filename);
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
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage());
        }

        return $pdf->stream($filename);
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function buildPdf(string $html): \Barryvdh\DomPDF\PDF
    {
        // dompdf does not support CSS custom properties (var(--name)).
        // We resolve them by extracting values from the :root block and
        // replacing every var(--name) occurrence in the full HTML string.
        $html = $this->resolveCssVariables($html);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('isPhpEnabled', false)
            ->setOption('isRemoteEnabled', true)   // allows base64 data-uri images (company logo)
            ->setOption('defaultMediaType', 'print')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf;
    }

    /**
     * Extract CSS variable definitions from :root{} blocks and resolve
     * every var(--name) reference in the HTML so dompdf can render them.
     *
     * Supports simple values and falls back gracefully if no :root block
     * is found (the HTML is returned unchanged).
     */
    private function resolveCssVariables(string $html): string
    {
        // Collect all CSS variable definitions from :root { ... } blocks.
        $variables = [];

        // Match one or more :root { ... } blocks (the template injects a
        // second one at the top of the <style> to override the accent color).
        if (preg_match_all('/:root\s*\{([^}]+)\}/s', $html, $rootMatches)) {
            foreach ($rootMatches[1] as $rootBody) {
                // Each variable line: --name: value;
                if (preg_match_all('/--([a-zA-Z0-9_-]+)\s*:\s*([^;]+);/', $rootBody, $varMatches, PREG_SET_ORDER)) {
                    foreach ($varMatches as $match) {
                        $variables['--' . trim($match[1])] = trim($match[2]);
                    }
                }
            }
        }

        if (empty($variables)) {
            return $html;
        }

        // Replace var(--name) with the resolved value.
        // We loop until no more substitutions are needed (handles nested vars).
        $maxPasses = 5;
        for ($i = 0; $i < $maxPasses; $i++) {
            $previous = $html;
            foreach ($variables as $varName => $value) {
                $pattern = '/var\(\s*' . preg_quote($varName, '/') . '\s*\)/';
                $html    = preg_replace($pattern, $value, $html);
            }
            if ($html === $previous) {
                break; // no more substitutions
            }
        }

        return $html;
    }

    private function buildFilename(Document $document): string
    {
        $ref  = $document->reference ?? $document->title ?? 'document-' . $document->id;
        $slug = Str::slug($ref);
        return $slug . '.pdf';
    }
}
