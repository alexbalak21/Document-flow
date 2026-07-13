<?php

namespace App\Http\Controllers;

use App\Models\CompanyAsset;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $companyPath = storage_path('app/company.json');
        $company = file_exists($companyPath)
            ? (json_decode(file_get_contents($companyPath), true) ?? [])
            : [];

        $logo = CompanyAsset::logo();

        return view('settings.company', compact('company', 'logo'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
        ]);

        // ── Handle logo upload ────────────────────────────────────────────
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            CompanyAsset::storeLogo($request->file('logo'));
        }

        if ($request->boolean('delete_logo')) {
            CompanyAsset::deleteLogo();
        }

        // ── Write all company fields to storage/app/company.json ──────────
        $data = [
            'name'                    => $request->name,
            'legal_form'              => $request->legal_form,
            'share_capital'           => $request->share_capital,
            'street'                  => $request->street,
            'city'                    => $request->city,
            'zip'                     => $request->zip,
            'country'                 => $request->country,
            'siren'                   => $request->siren,
            'siret'                   => $request->siret,
            'vat_number'              => $request->vat_number,
            'eori'                    => $request->eori,
            'email'                   => $request->email,
            'website'                 => $request->website,
            'default_currency'        => $request->default_currency,
            'default_currency_symbol' => $request->default_currency_symbol,
            'default_vat_rate'        => $request->default_vat_rate,
            'default_invoice_due_days'=> $request->default_invoice_due_days,
            'default_quote_valid_days'=> $request->default_quote_valid_days,
            'default_payment_method'  => $request->default_payment_method,
            'vat_mention'             => $request->vat_mention,
            'late_payment_rate'       => $request->late_payment_rate,
            'late_payment_flat_fee'   => $request->late_payment_flat_fee,
            'late_payment_text'       => $request->late_payment_text,
            'late_payment_fee_text'   => $request->late_payment_fee_text,
            'terms_text'              => $request->terms_text,
        ];

        file_put_contents(
            storage_path('app/company.json'),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return redirect()->route('settings.company')
            ->with('success', 'Company settings saved.');
    }
}