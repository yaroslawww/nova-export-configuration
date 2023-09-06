<?php

namespace NovaExportConfiguration\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Database\MigrateProcessor;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \NovaResourceDynamicExport\ServiceProvider::class,
            \NovaExportConfiguration\ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $migrator = new MigrateProcessor($this, [
            '--path'     => __DIR__.'/Fixtures/migrations',
            '--realpath' => true,
        ]);
        $migrator->up();
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // $app['config']->set('nova-export-configuration.some_key', 'some_value');
    }
}
