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

        'fr' => [
            'label'          => 'EUR — Banque Populaire (France)',
            'beneficiary'    => env('BANK_FR_BENEFICIARY',    'SAS NOVOCIB-CAV USD'),
            'bank_name'      => env('BANK_FR_NAME',           'BANQUE POPULAIRE AUVERGNE RHONE ALPES (BPAURA LYON GERLAND)'),
            'bank_address'   => env('BANK_FR_ADDRESS',        '115 avenue Lacassagne, Lyon, France'),
            'iban'           => env('BANK_FR_IBAN',           'FR76 1680 7004 0081 3911 3449 109'),
            'bic'            => env('BANK_FR_BIC',            'CCBPFRPPGRE'),
            'bank_code'      => env('BANK_FR_BANK_CODE',      '16807'),
            'branch_code'    => env('BANK_FR_BRANCH_CODE',    '00400'),
            'account_number' => env('BANK_FR_ACCOUNT',        '81391134491'),
            'rib_key'        => env('BANK_FR_RIB_KEY',        '09'),
        ],

        'int' => [
            'label'          => 'International Wire',
            'beneficiary'    => env('BANK_INT_BENEFICIARY',   'SAS NOVOCIB'),
            'bank_name'      => env('BANK_INT_NAME',          'BANQUE POPULAIRE AUVERGNE RHONE ALPES (BPAURA LYON GERLAND)'),
            'bank_address'   => env('BANK_INT_ADDRESS',       'LA CRIEE BAT ADMINISTRATIF, HALLE JEAN VOISIN, BOULEVARD DE CHATILLON, 62200 BOULOGNE-SUR-MER, France'),
            'iban'           => env('BANK_INT_IBAN',          'FR76 1680 7004 0081 3911 3449 109'),
            'bic'            => env('BANK_INT_BIC',           'CCBPFRPPGRE'),
            'bank_code'      => env('BANK_INT_BANK_CODE',     '16807'),
            'branch_code'    => env('BANK_INT_BRANCH_CODE',   '00400'),
            'account_number' => env('BANK_INT_ACCOUNT',       '81391134491'),
            'rib_key'        => env('BANK_INT_RIB_KEY',       '09'),
        ],

    ],

];
