<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $company = config('company');
        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $fields = [
            'COMPANY_NAME'                => $request->name,
            'COMPANY_LOGO'                => $request->logo,
            'COMPANY_LEGAL_FORM'          => $request->legal_form,
            'COMPANY_SHARE_CAPITAL'       => $request->share_capital,
            'COMPANY_STREET'              => $request->street,
            'COMPANY_CITY'                => $request->city,
            'COMPANY_ZIP'                 => $request->zip,
            'COMPANY_COUNTRY'             => $request->country,
            'COMPANY_SIREN'               => $request->siren,
            'COMPANY_SIRET'               => $request->siret,
            'COMPANY_VAT_NUMBER'          => $request->vat_number,
            'COMPANY_EORI'                => $request->eori,
            'COMPANY_EMAIL'               => $request->email,
            'COMPANY_WEBSITE'             => $request->website,
            'COMPANY_CURRENCY'            => $request->default_currency,
            'COMPANY_CURRENCY_SYMBOL'     => $request->default_currency_symbol,
            'COMPANY_VAT_RATE'            => $request->default_vat_rate,
            'COMPANY_INVOICE_DUE_DAYS'    => $request->default_invoice_due_days,
            'COMPANY_QUOTE_VALID_DAYS'    => $request->default_quote_valid_days,
            'COMPANY_PAYMENT_METHOD'      => $request->default_payment_method,
            'COMPANY_VAT_MENTION'         => $request->vat_mention,
            'COMPANY_LATE_PAYMENT_RATE'   => $request->late_payment_rate,
            'COMPANY_LATE_PAYMENT_FLAT_FEE' => $request->late_payment_flat_fee,
            'COMPANY_LATE_PAYMENT_TEXT'   => $request->late_payment_text,
            'COMPANY_LATE_PAYMENT_FEE_TEXT' => $request->late_payment_fee_text,
            'COMPANY_TERMS_TEXT'          => $request->terms_text,
        ];

        $this->writeEnv($fields);

        Artisan::call('config:clear');

        return redirect()->route('settings.company')
            ->with('success', 'Company settings saved.');
    }

    /**
     * Write or update key=value pairs in the .env file.
     */
    private function writeEnv(array $fields): void
    {
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($fields as $key => $value) {
            // Wrap value in quotes if it contains spaces or special chars
            $escaped = $this->escapeEnvValue((string) $value);

            if (preg_match("/^{$key}=/m", $envContent)) {
                // Key exists — replace it
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escaped}",
                    $envContent
                );
            } else {
                // Key missing — append it
                $envContent .= "\n{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    private function escapeEnvValue(string $value): string
    {
        // If value contains spaces, commas, special chars — wrap in double quotes
        if (preg_match('/[\s,;#"\'\\\\]/', $value) || $value === '') {
            $value = '"' . addslashes($value) . '"';
        }
        return $value;
    }
}
