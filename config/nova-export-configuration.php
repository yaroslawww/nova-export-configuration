<?php

return [
    'tables' => [
        'export_configs'             => 'export_configs',
    ],

    'defaults' => [
        'disk'               => 'exports_configured',
        'queue'              => 'export',
    ],
];
