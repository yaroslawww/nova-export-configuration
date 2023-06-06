<?php

return [
    'tables' => [
        'export_configs'             => 'export_configs',
        'export_config_stored_files' => 'export_config_stored_files',
    ],

    'defaults' => [
        'disk'               => 'exports_configured',
        'disk_export_action' => 'exports',
        'queue'              => 'export',
        'download_route'     => 'download.exports',
    ],
];
