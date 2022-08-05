<?php

return [
    'tables' => [
        'export_configs'             => 'export_configs',
        'export_config_stored_files' => 'export_config_stored_files',
    ],

    'defaults' => [
        'disk' => 'exports_configured',
        'queue' => 'export',
        'download_route' => 'download.exports',
    ]
];
