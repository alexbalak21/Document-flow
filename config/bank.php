<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Default bank account
    |--------------------------------------------------------------------------
    | 'fr'  — EUR payments, French domestic / EU clients
    | 'int' — USD / international payments
    */
    'default' => env('BANK_DEFAULT', 'int'),

    'accounts' => [

        /*
        |--------------------------------------------------------------------------
        | FRENCH / EUR ACCOUNT
        |--------------------------------------------------------------------------
        | IBAN: FR76 1680 7004 0081 0876 0421 151
        | Beneficiary: SAS NOVOCIB
        | Bank address: 215 Avenue Jean Jaurès, 69007 Lyon
        */
        'fr' => [
            'label'          => 'EUR - Banque Populaire, France',
            'beneficiary'    => env('BANK_FR_BENEFICIARY',    'SAS NOVOCIB'),
            'bank_name'      => env('BANK_FR_NAME',           'BANQUE POPULAIRE AUVERGNE RHÔNE ALPES (BPAURA)'),
            'bank_address'   => env('BANK_FR_ADDRESS',        '215 Avenue Jean Jaurès, 69007 Lyon, France'),
            'company_address'=> env('BANK_FR_COMPANY_ADDR',   'LA CRIEE BAT ADMINISTRATIF, HALLE JEAN VOISIN, BOULEVARD DE CHATILLON, 62200 BOULOGNE-SUR-MER, France'),
            'iban'           => env('BANK_FR_IBAN',           'FR76 1680 7004 0081 0876 0421 151'),
            'bic'            => env('BANK_FR_BIC',            'CCBPFRPPGRE'),
            'bank_code'      => env('BANK_FR_BANK_CODE',      '16807'),
            'branch_code'    => env('BANK_FR_BRANCH_CODE',    '00400'),
            'account_number' => env('BANK_FR_ACCOUNT',        '81087604211'),
            'rib_key'        => env('BANK_FR_RIB_KEY',        '51'),
        ],

        /*
        |--------------------------------------------------------------------------
        | INTERNATIONAL / USD ACCOUNT
        |--------------------------------------------------------------------------
        | IBAN: FR76 1680 7004 0081 3911 3449 109
        | Beneficiary: SAS NOVOCIB-CAV USD
        | Bank address: 215 Avenue Jean Jaurès, 69007 Lyon
        */
        'int' => [
            'label'          => 'International Wire (USD)',
            'beneficiary'    => env('BANK_INT_BENEFICIARY',   'SAS NOVOCIB-CAV USD'),
            'bank_name'      => env('BANK_INT_NAME',          'BANQUE POPULAIRE AUVERGNE RHÔNE ALPES (BPAURA)'),
            'bank_address'   => env('BANK_INT_ADDRESS',       '215 Avenue Jean Jaurès, 69007 Lyon, France'),
            'company_address'=> env('BANK_INT_COMPANY_ADDR',  'LA CRIEE BAT ADMINISTRATIF, HALLE JEAN VOISIN, BOULEVARD DE CHATILLON, 62200 BOULOGNE-SUR-MER, France'),
            'iban'           => env('BANK_INT_IBAN',          'FR76 1680 7004 0081 3911 3449 109'),
            'bic'            => env('BANK_INT_BIC',           'CCBPFRPPGRE'),
            'bank_code'      => env('BANK_INT_BANK_CODE',     '16807'),
            'branch_code'    => env('BANK_INT_BRANCH_CODE',   '00400'),
            'account_number' => env('BANK_INT_ACCOUNT',       '81391134491'),
            'rib_key'        => env('BANK_INT_RIB_KEY',       '09'),
        ],

    ],
];
