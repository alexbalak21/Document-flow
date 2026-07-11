<?php

namespace App\Http\Controllers;

use App\Models\CompanyAsset;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $company = config('company');
        $logo    = CompanyAsset::logo();

        return view('settings.company', compact('company', 'logo'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => ['nullable', 'file', 'image', 'max:2048'], // max 2MB
        ]);

        // ── Handle logo upload ────────────────────────────────────────────
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            CompanyAsset::storeLogo($request->file('logo'));
        }

        if ($request->boolean('delete_logo')) {
            CompanyAsset::deleteLogo();
        }

        // ── Write text fields to .env (no logo, no long texts) ───────────
        $fields = [
            'COMPANY_NAME'              => $request->name,
            'COMPANY_LEGAL_FORM'        => $request->legal_form,
            'COMPANY_SHARE_CAPITAL'     => $request->share_capital,
            'COMPANY_STREET'            => $request->street,
            'COMPANY_CITY'              => $request->city,
            'COMPANY_ZIP'               => $request->zip,
            'COMPANY_COUNTRY'           => $request->country,
            'COMPANY_SIREN'             => $request->siren,
            'COMPANY_SIRET'             => $request->siret,
            'COMPANY_VAT_NUMBER'        => $request->vat_number,
            'COMPANY_EORI'              => $request->eori,
            'COMPANY_EMAIL'             => $request->email,
            'COMPANY_WEBSITE'           => $request->website,
            'COMPANY_CURRENCY'          => $request->default_currency,
            'COMPANY_CURRENCY_SYMBOL'   => $request->default_currency_symbol,
            'COMPANY_VAT_RATE'          => $request->default_vat_rate,
            'COMPANY_INVOICE_DUE_DAYS'  => $request->default_invoice_due_days,
            'COMPANY_QUOTE_VALID_DAYS'  => $request->default_quote_valid_days,
            'COMPANY_PAYMENT_METHOD'    => $request->default_payment_method,
        ];

        $this->writeEnv($fields);

        // ── Long text fields go to storage JSON (not .env) ────────────────
        $longFields = [
            'vat_mention'           => $request->vat_mention,
            'late_payment_rate'     => $request->late_payment_rate,
            'late_payment_flat_fee' => $request->late_payment_flat_fee,
            'late_payment_text'     => $request->late_payment_text,
            'late_payment_fee_text' => $request->late_payment_fee_text,
            'terms_text'            => $request->terms_text,
        ];

        file_put_contents(
            storage_path('app/company_extra.json'),
            json_encode($longFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return redirect()->route('settings.company')
            ->with('success', 'Company settings saved.');
    }

    private function writeEnv(array $fields): void
    {
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($fields as $key => $value) {
            $escaped = $this->escapeEnvValue((string) $value);

            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escaped}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$escaped}";
            }
        }

        // Remove COMPANY_LOGO from .env if it exists (moved to DB)
        $envContent = preg_replace('/^COMPANY_LOGO=.*$/m', '', $envContent);

        file_put_contents($envPath, $envContent);
    }

    private function escapeEnvValue(string $value): string
    {
        if (preg_match('/[\s,;#"\'\\\\]/', $value) || $value === '') {
            $value = '"' . addslashes($value) . '"';
        }
        return $value;
    }
}
