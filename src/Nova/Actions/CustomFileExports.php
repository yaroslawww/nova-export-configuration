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
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Concerns\WithDisk;
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

    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var CustomExport $exportable */
        $exportable = NovaExportConfig::customExportsByKey($fields->get('export'));
        if (!$exportable) {
            return Action::danger(__('Exportable config not found'));
        }

        $writerType = $fields->get('writer_type');

        $dbExport = ExportStoredFile::init(
            'custom-export',
            $this->getDisk() ?: $exportable::diskName(),
            date('Y/m/d/') . Str::uuid() . '.' . Str::lower($writerType ?: 'xlsx'),
            $fields->get('filename', $exportable::name()) . '.' . Str::lower($writerType ?: 'xlsx'),
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
                ->displayUsingLabels(),
        ], $this->actionFields);
    }
}
