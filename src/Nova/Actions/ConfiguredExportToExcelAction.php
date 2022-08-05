<?php

namespace NovaExportConfiguration\Nova\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\ActionRequest;
use NovaExportConfiguration\Models\ExportStoredFile;
use NovaExportConfiguration\NovaExportConfig;

class ConfiguredExportToExcelAction extends \Maatwebsite\LaravelNovaExcel\Actions\ExportToExcel
{
    use WithQueue;

    public $showOnIndex = false;

    public $showInline = true;

    public $showOnDetail = true;

    public function name()
    {
        return __('Export Config');
    }

    protected function withDefaultFilename(ActionRequest $request)
    {
        $model = $request->findModelOrFail($request->resources);
        $this->withFilename(Str::kebab($model->name).date('-Ymd').'.'.$this->getDefaultExtension());
    }


    public function handle(ActionRequest $request, Action $exportable): array
    {
        /** @var \NovaExportConfiguration\Models\ExportConfig $model */
        $model = $request->findModelOrFail($request->resources);

        $repo = NovaExportConfig::getRepositories()->getByName($model->type);
        if (! $repo) {
            return Action::danger(__('Export repository not found.'));
        }
        $resource = $request->resource();
        $type = $resource::uriKey();
        $name = $this->getFilename();
        $filename = date('Y/m/d/').Str::uuid().'.'.$this->getDefaultExtension();
        $disk = $this->getDisk() ?: $repo->disk();
        $filters = $request->filters;
        $fields = $request->resolveFields();

        $dbExport = new ExportStoredFile();
        $dbExport->type = $type;
        $dbExport->disk = $disk;
        $dbExport->path = $filename;
        $dbExport->name = $name;
        $dbExport->meta->setAttribute('fields', $fields);
        $dbExport->meta->setAttribute('filters', $filters);
        if ($user = Auth::user()) {
            $dbExport->meta->setAttribute('author.type', $user->getMorphClass());
            $dbExport->meta->setAttribute('author.id', $user->getKey());
        }

        $export = $repo->export($model, serialize($dbExport));
        $queueName = $this->queueName ?: $repo->queue();
        if ($queueName) {
            $export->queue(
                $filename,
                $disk,
                $this->getWriterType()
            )->allOnQueue($queueName);
        } else {
            $export->store(
                $filename,
                $disk,
                $this->getWriterType()
            );
        }


        return Action::message(__('Export started.'));
    }
}
