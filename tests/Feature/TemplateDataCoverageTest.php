<?php

namespace Tests\Feature;

use App\Http\Controllers\DocumentController;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Renders every installed template with realistic sample data — the same
 * way DocumentController::preview()/store() does — and asserts that every
 * field defined in that template's form.json (and every customer/product
 * entity field it declares) actually shows up in the generated HTML.
 *
 * This is a direct regression test for bugs where a field is collected by
 * the form and saved to json_data, but the template.html never references
 * it (e.g. delivery_fee / hs_code silently missing from the invoice PDF).
 */
class TemplateDataCoverageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fields that are intentionally NOT printed verbatim anywhere in the
     * document because they drive logic/selection instead of display:
     *   - bank_account: picks which bank block (int/fr) gets injected via
     *     bank_iban/bank_label/etc — the raw code "int"/"fr" never appears.
     *   - vat_rate: normally only feeds the computed vat_amount. facture-fr
     *     does print it inline ("TVA (20%)") so it's excluded per-slug below
     *     rather than globally, to keep that one honest.
     *
     * If you add a field here, add a comment explaining why it's exempt —
     * this list should stay short and deliberate, not a dumping ground.
     */
    private const GLOBALLY_EXCLUDED_FIELDS = ['bank_account'];

    private const SLUG_EXCLUDED_FIELDS = [
        'invoice'    => ['vat_rate'],
        'quote'      => ['vat_rate'],
        'proposal'   => ['vat_rate'],
        'quote-fr'   => ['vat_rate'],
        // facture-fr intentionally left out: it DOES print vat_rate.
    ];

    /**
     * @dataProvider templateSlugProvider
     */
    #[DataProvider('templateSlugProvider')]
    public function test_all_form_and_entity_fields_appear_in_generated_document(string $slug): void
    {
        $dir = storage_path("app/templates/{$slug}");

        $manifest = json_decode(file_get_contents("{$dir}/manifest.json"), true);
        $formPath = $dir . '/' . ($manifest['form'] ?? 'form.json');
        $form     = json_decode(file_get_contents($formPath), true);

        [$data, $expectedValues] = $this->buildSampleData($form, $manifest);

        $excluded = array_merge(
            self::GLOBALLY_EXCLUDED_FIELDS,
            self::SLUG_EXCLUDED_FIELDS[$slug] ?? []
        );

        $type = new DocumentType(['slug' => $slug]);

        $html = app(DocumentController::class)->renderHtml($type, $data, 'en');

        foreach ($expectedValues as $field => $value) {
            if (in_array($field, $excluded, true)) {
                continue;
            }

            $this->assertStringContainsString(
                (string) $value,
                $html,
                "Field '{$field}' (value '{$value}') from {$slug}/form.json was submitted "
                . "but does not appear anywhere in the generated {$slug} document. "
                . "Check that template.html actually references {{{$field}}}."
            );
        }
    }

    public static function templateSlugProvider(): array
    {
        $templatesDir = storage_path('app/templates');
        $slugs = [];

        foreach (scandir($templatesDir) as $entry) {
            $dir = "{$templatesDir}/{$entry}";
            if (! is_dir($dir) || in_array($entry, ['.', '..'])) {
                continue;
            }
            if (file_exists("{$dir}/manifest.json") && file_exists("{$dir}/template.html")) {
                $slugs[$entry] = [$entry];
            }
        }

        return $slugs;
    }

    /**
     * Build a $data array with a unique, greppable sentinel value for every
     * field the form declares (plus entity fields for customer/product),
     * along with the flags renderHtml needs to actually walk the
     * {{#no_fx}} / items-table branches instead of skipping them.
     *
     * @return array{0: array, 1: array} [$data, $expectedValues]
     */
    private function buildSampleData(array $form, array $manifest): array
    {
        $data = [
            // Force the single-currency branch so the items table (and any
            // fields nested inside it) actually render.
            'no_fx' => '1',
        ];
        $expected = [];

        foreach ($form as $section) {
            foreach ($section['fields'] as $field) {
                $name  = $field['name'];
                $value = $this->sampleValueFor($field);
                $data[$name]     = $value;
                $expected[$name] = $value;
            }
        }

        // Fields DocumentController::computeTotals() would normally add.
        // We set them directly so the items/total block renders without
        // needing the private computeTotals() method.
        $data['subtotal']   = $data['subtotal']   ?? '999.99';
        $data['vat_amount'] = $data['vat_amount'] ?? '100.00';
        $data['total']      = $data['total']      ?? '1099.99';

        // Entity fields (customer_*, product_*) — these are injected by
        // EntityResolver::saveFromRequest() in the real flow, so we
        // replicate that shape here directly.
        foreach ($manifest['entities'] ?? [] as $entity) {
            $fields = match ($entity) {
                'customer' => ['name', 'company', 'department', 'street', 'city', 'zip', 'country', 'phone', 'email', 'vat_number'],
                'product'  => ['reference', 'name', 'description', 'product_unit', 'quantity', 'unit_price'],
                default    => [],
            };

            foreach ($fields as $field) {
                $key   = "{$entity}_{$field}";
                $value = "SENTINEL_{$key}";
                $data[$key]     = $value;
                $expected[$key] = $value;
            }
        }

        return [$data, $expected];
    }

    private function sampleValueFor(array $field): string
    {
        $name = $field['name'];

        return match ($field['type']) {
            'number'   => '424.24',
            'date'     => '2026-08-01',
            'select'   => (string) ($field['options'][0] ?? "SENTINEL_{$name}"),
            'textarea' => "SENTINEL_{$name}_multiline_text",
            default    => "SENTINEL_{$name}",
        };
    }
}
