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
use Maatwebsite\Excel\Facades\Excel;
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
        $type       = 'custom-export';
        $name       = $fields->get('filename', $exportable::name()) . '.' . Str::lower($writerType ?: 'xlsx');
        $filename   = date('Y/m/d/') . Str::uuid() . '.' . Str::lower($writerType ?: 'xlsx');
        $disk       = $this->getDisk() ?: $exportable::diskName();

        $response = Excel::store(
            $exportable,
            $filename,
            $disk,
            $writerType
        );

        if (false === $response) {
            return Action::danger(__('Resource could not be exported.'));
        }

        $dbExport       = new ExportStoredFile();
        $dbExport->type = $type;
        $dbExport->disk = $disk;
        $dbExport->path = $filename;
        $dbExport->name = $name;
        if ($user = Auth::user()) {
            $dbExport->meta->toMorph('author', $user);
        }

        $exportable->setFileModelData(serialize($dbExport));

        if ($queueName = $this->getQueue($exportable::queueName())) {
            $exportable->queue(
                $filename,
                $disk,
                $writerType
            )->allOnQueue($queueName);

            return Action::message(__('Request added to queue. Please wait a while to complete it.'));
        }

        $exportable->store(
            $filename,
            $disk,
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
