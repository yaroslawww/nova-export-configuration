<?php

namespace NovaExportConfiguration;

use NovaExportConfiguration\Nova\Resources\ExportConfiguration;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-export-configuration');

        if ($this->app->runningInConsole()) {
            if (NovaExportConfig::$runsMigrations) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            }

            $this->publishes([
                __DIR__ . '/../config/nova-export-configuration.php' => config_path('nova-export-configuration.php'),
            ], 'config');
        }

        $this->configureExportResource();
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-export-configuration.php', 'nova-export-configuration');
    }

    public function configureExportResource()
    {
        ExportConfiguration::$model = NovaExportConfig::$configurationModelClass;
        ExportConfiguration::$group = __('Export');
    }
}
