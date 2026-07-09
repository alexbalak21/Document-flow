<?php

return [

    // Identity
    'name'          => env('COMPANY_NAME', 'NOVOCIB SAS'),
    'logo'          => env('COMPANY_LOGO', ''),   // relative path under public/

    // Legal
    'legal_form'    => env('COMPANY_LEGAL_FORM',   'SAS, société par actions simplifiée'),
    'share_capital' => env('COMPANY_SHARE_CAPITAL', '260 158,00 €'),

    // Address
    'street'        => env('COMPANY_STREET',  'BD de Chatillon, Quai Jean Voisin'),
    'city'          => env('COMPANY_CITY',    'Boulogne-sur-Mer'),
    'zip'           => env('COMPANY_ZIP',     '62200'),
    'country'       => env('COMPANY_COUNTRY', 'France'),

    // Official identifiers
    'siren'         => env('COMPANY_SIREN',      '482 379 377'),
    'siret'         => env('COMPANY_SIRET',      '482 379 377 00047'),
    'vat_number'    => env('COMPANY_VAT_NUMBER', 'FR90 482 379 377'),
    'eori'          => env('COMPANY_EORI',       'FR48237937700047'),

    // Contact
    'email'         => env('COMPANY_EMAIL',   'lbalakireva@novocib.com'),
    'website'       => env('COMPANY_WEBSITE', 'https://novocib.com'),

    // Accounting defaults
    'default_currency'        => env('COMPANY_CURRENCY',        'EUR'),
    'default_currency_symbol' => env('COMPANY_CURRENCY_SYMBOL', '€'),
    'default_vat_rate'        => env('COMPANY_VAT_RATE',        0),
    'default_invoice_due_days'=> env('COMPANY_INVOICE_DUE_DAYS', 30),
    'default_quote_valid_days'=> env('COMPANY_QUOTE_VALID_DAYS', 30),
    'default_payment_method'  => env('COMPANY_PAYMENT_METHOD',  'Bank Transfer (Wire)'),

    // Legal mentions
    'vat_mention'           => env('COMPANY_VAT_MENTION',
        'VAT not applicable - export outside the European Union (Article 259 of the French Tax Code)'),
    'late_payment_rate'     => env('COMPANY_LATE_PAYMENT_RATE',     4.5),
    'late_payment_flat_fee' => env('COMPANY_LATE_PAYMENT_FLAT_FEE', 40),
    'late_payment_text'     => env('COMPANY_LATE_PAYMENT_TEXT',
        'Late payment penalties apply from the due date.'),
    'late_payment_fee_text' => env('COMPANY_LATE_PAYMENT_FEE_TEXT',
        'A fixed recovery fee may also apply.'),
    'terms_text'            => env('COMPANY_TERMS_TEXT',
        'Payment is due according to the terms listed on the document.'),

];
