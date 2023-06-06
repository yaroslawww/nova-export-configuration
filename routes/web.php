<?php


use Illuminate\Support\Facades\Route;
use NovaExportConfiguration\NovaExportConfig;

Route::group(NovaExportConfig::routeConfiguration(), function () {
    Route::get(
        '{file}',
        \NovaExportConfiguration\Http\Controllers\DownloadExportController::class
    )
        ->where('file', '^[\.a-zA-Z0-9-_\/]+$')
        ->name(config('nova-export-configuration.defaults.download_route'));
});
