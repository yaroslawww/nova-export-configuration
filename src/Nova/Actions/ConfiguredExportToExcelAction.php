<?php

namespace NovaExportConfiguration\Nova\Actions;

use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\ActionRequest;
use Maatwebsite\LaravelNovaExcel\Actions\ExportToExcel;
use NovaExportConfiguration\Models\ExportStoredFile;
use NovaExportConfiguration\NovaExportConfig;

class ConfiguredExportToExcelAction extends ExportToExcel
{
    use WithQueue;

    public $showOnIndex = false;

    public $showInline = true;

    public $showOnDetail = true;

    public function name(): string
    {
        return __('Export Config');
    }

    protected function withDefaultFilename(ActionRequest $request): void
    {
        $model = $request->findModelOrFail($request->resources);
        $this->withFilename(Str::kebab($model->name) . date('-Ymd') . '.' . $this->getDefaultExtension());
    }


    public function handle(ActionRequest $request, Action $exportable)
    {
        /** @var \NovaExportConfiguration\Models\ExportConfig $model */
        $model = $request->findModelOrFail($request->resources);

        $repo = NovaExportConfig::getRepositories()->getByName($model->type);
        if (!$repo) {
            return Action::danger(__('Export repository not found.'));
        }

        $dbExport = ExportStoredFile::init(
            $request->resource()::uriKey(),
            $this->getDisk() ?: $repo->disk(),
            date('Y/m/d/') . Str::uuid() . '.' . $this->getDefaultExtension(),
            $this->getFilename(),
            function (ExportStoredFile $model) use ($request) {
                $model->meta->setAttribute('fields', $request->resolveFields());
                $model->meta->setAttribute('filters', $request->filters);
                if ($user = $request->user()) {
                    $model->meta->toMorph('author', $user);
                }
            }
        );

        $export = $repo->export($model, $dbExport);

        if ($queueName = $this->getQueue($repo->queue())) {
            $export->queue(
                $dbExport->path,
                $dbExport->disk,
                $this->getWriterType()
            )->allOnQueue($queueName);
        } else {
            $export->store(
                $dbExport->path,
                $dbExport->disk,
                $this->getWriterType()
            );
        }

        return Action::message(__('Export started.'));
    }
}
