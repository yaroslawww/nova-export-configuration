<?php

namespace NovaExportConfiguration\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Concerns\WithDisk;
use Maatwebsite\LaravelNovaExcel\Concerns\WithFilename;
use Maatwebsite\LaravelNovaExcel\Concerns\WithWriterType;
use Maatwebsite\LaravelNovaExcel\Interactions\AskForFilename;
use Maatwebsite\LaravelNovaExcel\Interactions\AskForWriterType;
use NovaExportConfiguration\Export\CustomExport;
use NovaExportConfiguration\Models\ExportStoredFile;
use NovaExportConfiguration\NovaExportConfig;

class CustomFileExports extends Action
{
    use InteractsWithQueue, Queueable;
    use AskForFilename,
        AskForWriterType,
        WithDisk,
        WithFilename,
        WithWriterType,
        WithQueue;

    public $standalone = true;

    public $showOnIndex = true;

    public $showInline = false;

    public $showOnDetail = false;

    protected $actionFields = [];

    protected array $exportsList = [];

    public function __construct(array $exportsList = [])
    {
        $this->exportsList = $exportsList;
    }

    public function exportsList(array $exportsList = []): static
    {
        $this->exportsList = $exportsList;

        return $this;
    }


    public function name()
    {
        return __('Custom Exports');
    }

    public function handleRequest(ActionRequest $request)
    {
        $this->handleWriterType($request);
        $this->handleFilename($request);

        return parent::handleRequest($request);
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $exportName = $fields->get('export');

        if (!$exportName) {
            return Action::danger(__('Export not selected.'));
        }

        /** @var CustomExport $exportable */
        $exportable = NovaExportConfig::customExportsByKey($exportName);
        if (!$exportable) {
            return Action::danger(__('Exportable config not found'));
        }

        $writerType = $fields->get('writer_type');

        $dbExport = ExportStoredFile::init(
            'custom-export',
            $this->getDisk() ?: $exportable::diskName(),
            date('Y/m/d/') . Str::uuid() . '.' . $this->getDefaultExtension(),
            $this->getFilename(),
            function ($file) {
                if ($user = Auth::user()) {
                    $file->meta->toMorph('author', $user);
                }
            }
        );


        $exportable->useStoreFile($dbExport);

        if ($queueName = $this->getQueue($exportable::queueName())) {
            $exportable->queue(
                $dbExport->path,
                $dbExport->disk,
                $writerType
            )->allOnQueue($queueName);

            return Action::message(__('Request added to queue. Please wait a while to complete it.'));
        }

        $exportable->store(
            $dbExport->path,
            $dbExport->disk,
            $writerType
        );

        return Action::message(__('Data exported to file.'));
    }

    public function fields(NovaRequest $request)
    {

        return array_merge([
            Select::make('Export', 'export')
                ->options($this->exportsList)
                ->required()
                ->displayUsingLabels()
                ->rules(['required']),
        ], $this->actionFields);
    }

    protected function getDefaultExtension(): string
    {
        return $this->getWriterType() ? strtolower($this->getWriterType()) : 'xlsx';
    }
}
