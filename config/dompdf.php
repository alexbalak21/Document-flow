<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set the default paper size and orientation. Options for paper are listed
    | at https://github.com/dompdf/dompdf/blob/master/src/Adapter/CPDF.php
    |
    */

    'show_warnings' => false,

    'public_path' => null,

    'convert_entities' => true,

    'options' => [

        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],
        'log_output_file' => null,
        'is_php_enabled' => false,
        'is_remote_enabled' => true,
        'is_javascript_enabled' => false,
        'is_html5_parser_enabled' => true,
        'is_font_subsetting_enabled' => true,
        'default_media_type' => 'print',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'helvetica',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],

];
