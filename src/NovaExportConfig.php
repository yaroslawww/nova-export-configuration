<?php

namespace NovaExportConfiguration;

use Closure;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaExportConfiguration\Export\CustomExport;
use NovaExportConfiguration\Models\ExportConfig;
use NovaExportConfiguration\Repositories\ExportRepository;
use NovaExportConfiguration\Repositories\ExportRepositoryCollection;

class NovaExportConfig
{
    /**
     * Export repositories used by application
     */
    protected static ?ExportRepositoryCollection $repositories = null;

    /**
     * Export configuration model class
     */
    public static string $configurationModelClass = \NovaExportConfiguration\Models\ExportConfig::class;

    /**
     * Indicates if NovaExportConfiguration migrations will be run.
     */
    public static bool $runsMigrations = true;

    /**
     * Additional actions
     */
    public static Closure|array|null $configurationActionsCallback = null;

    /**
     * @var array
     */
    public static array $customExports = [];


    public static function ignoreMigrations(): static
    {
        static::$runsMigrations = false;

        return new static;
    }

    public static function useConfigurationModelClass(string $class): static
    {
        if (!is_a($class, ExportConfig::class, true)) {
            throw new \InvalidArgumentException('Class should extend ExportConfig');
        }

        static::$configurationModelClass = $class;

        return new static;
    }

    public static function useRepository(string|ExportRepository|array $repository): static
    {
        if (is_null(static::$repositories)) {
            static::$repositories = new ExportRepositoryCollection();
        }

        if (is_array($repository)) {
            foreach ($repository as $repo) {
                static::useRepository($repo);
            }

            return new static;
        }

        if (is_string($repository)) {
            $repository = new $repository();
        }

        if ($repository instanceof ExportRepository) {
            static::$repositories->add($repository);
            static::$repositories = static::$repositories->uniqueNames();
        }

        return new static;
    }

    public static function getRepositories(): ExportRepositoryCollection
    {
        if (is_null(static::$repositories)) {
            static::$repositories = new ExportRepositoryCollection();
        }

        return self::$repositories;
    }

    public static function useConfigurationActions(Closure|array $configurationActionsCallback): static
    {
        self::$configurationActionsCallback = $configurationActionsCallback;

        return new static;
    }

    public static function configurationActions(NovaRequest $request): array
    {
        if (is_callable(static::$configurationActionsCallback)) {
            return call_user_func(static::$configurationActionsCallback, $request);
        }

        return [];
    }

    public static function typeOptions(): array
    {
        return static::getRepositories()
                        ->mapWithKeys(fn ($repo) => [$repo->name() => $repo->label()])
                        ->toArray();
    }



    public static function useCustomExportsToFile(string|CustomExport|array $customExport): static
    {
        if (is_array($customExport)) {
            foreach ($customExport as $customExportItem) {
                static::useCustomExportsToFile($customExportItem);
            }

            return new static;
        }

        if (!is_string($customExport)) {
            $customExport = $customExport::class;
        }

        if (!is_subclass_of($customExport, CustomExport::class)) {
            throw new \Exception('Custom export should be subclass of ' . CustomExport::class);
        }

        static::$customExports[] = $customExport;

        return new static;
    }

    public static function customExportsOptions(): array
    {
        $exportsList   = [];
        $customExports = NovaExportConfig::$customExports;
        if(!empty($customExports)) {
            /** @var CustomExport $customExport */
            foreach ($customExports as $customExport) {
                $exportsList[$customExport::key()] = $customExport::name();
            }
        }

        return $exportsList;
    }

    public static function customExportsByKey(string $key): ?CustomExport
    {
        $customExports = NovaExportConfig::$customExports;
        if(!empty($customExports)) {
            /** @var CustomExport $customExport */
            foreach ($customExports as $customExport) {
                if($customExport::key() != $key) {
                    continue;
                }

                return new $customExport;
            }
        }

        return null;
    }
}
