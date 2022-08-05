<?php

namespace NovaExportConfiguration\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use NovaExportConfiguration\Export\ConfiguredExport;
use NovaExportConfiguration\Export\ExportQuery;
use NovaExportConfiguration\Export\NovaResourceConfig;
use NovaExportConfiguration\Models\ExportConfig;

abstract class ExportRepository
{
    abstract public function name(): string;

    public function label(): string
    {
        return Str::ucfirst(Str::snake(Str::camel($this->name()), ' '));
    }

    public function disk(): string
    {
        return config('nova-export-configuration.defaults.disk');
    }

    public function queue(): string
    {
        return config('nova-export-configuration.defaults.queue');
    }

    abstract public function exportFileClass(): string;

    abstract public function exportQueryClass(): string;

    abstract public function novaResourceConfigClass(): string;

    public function exportFile(ExportConfig $model): ConfiguredExport
    {
        $exportFileClass = $this->exportFileClass();

        return new $exportFileClass($this->exportQuery($model));
    }

    public function exportQuery(ExportConfig $model): ExportQuery
    {
        $exportQueryClass = $this->exportQueryClass();

        return new $exportQueryClass($model);
    }

    public function novaResourceConfig(): NovaResourceConfig
    {
        $novaResourceConfigClass= $this->novaResourceConfigClass();

        return new $novaResourceConfigClass();
    }

    public function export(ExportConfig $model, string $serialisedFileData)
    {
        return $this->exportFile($model)
                    ->setFileModelData($serialisedFileData)
                    ->setNotificationUser(Auth::user());
    }

    abstract public function isFilterKey(string $key): bool;

    abstract public function modelSetAttribute(ExportConfig $model, $key, $value);

    abstract public function modelGetAttribute(ExportConfig $model, $key);

    public function regenerateConfigurationData(ExportConfig $model, bool $persist): void
    {
        if ($model->exists && $model->getKey() && $this->pivotRelation($model)) {
            $this->rebuildAttachedPivots(dispatch(function () use ($model) {
                $pivotRelation = $this->pivotRelation($model);
                $query = $this->exportQuery($model)->query();
                $pivotRelation->detach();
                $query->select('id')
                      ->chunk(1000, function ($models) use ($pivotRelation) {
                          $pivotRelation->syncWithoutDetaching($models->pluck('id')->toArray());
                      });
            }));
        }
    }

    protected function pivotRelation(ExportConfig $model): ?BelongsToMany
    {
        return null;
    }

    protected function rebuildAttachedPivots(PendingDispatch $pendingDispatch): void
    {

    }
}
