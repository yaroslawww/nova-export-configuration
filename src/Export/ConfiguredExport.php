<?php

namespace NovaExportConfiguration\Export;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use NovaExportConfiguration\Models\ExportStoredFile;

abstract class ConfiguredExport implements FromQuery, WithMapping, WithEvents, WithHeadings, WithCustomChunkSize
{
    use Exportable;

    protected ExportQuery $exportQuery;

    protected int $chunkSize = 500;

    protected ?string $downloadLink = null;

    protected string $fileModelData = '';

    protected ?string $notificationUserClass = null;
    protected ?int $notificationUserId       = null;

    public function __construct(ExportQuery $exportQuery)
    {
        $this->exportQuery = $exportQuery;
    }

    abstract public function headings(): array;

    abstract public function map($row): array;

    public function query()
    {
        return $this->exportQuery->query();
    }

    public function setFileModelData(string $fileModelData): static
    {
        $this->fileModelData = $fileModelData;

        return $this;
    }

    public function setNotificationUser(?Model $notificationUser = null): static
    {
        $this->notificationUserId    = $notificationUser?->getKey();
        $this->notificationUserClass = $notificationUser?->getMorphClass();

        return $this;
    }

    public function notificationUser(): ?Model
    {
        $class = Relation::getMorphedModel($this->notificationUserClass);
        if ($class) {
            return $class::find($this->notificationUserId);
        }

        return null;
    }

    public function setChunkSize(int $chunkSize): static
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function setDownloadLink(?string $downloadLink): static
    {
        $this->downloadLink = $downloadLink;

        return $this;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                try {
                    $this->exportQuery->getConfigurationModel()->update([
                        'last_export_at' => Carbon::now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }

                /** @var ExportStoredFile $fileModel */
                $fileModel = null;
                if ($this->fileModelData) {
                    $fileModel = unserialize($this->fileModelData);
                    if ($fileModel instanceof ExportStoredFile) {
                        $fileModel->save();
                    }
                }
                $user = $this->notificationUser();
                if ($user && $fileModel?->exists) {
                    $user->notify(
                        NovaNotification::make()
                                        ->message("File {$fileModel->name} exported")
                                        ->action('Download', URL::remote($this->downloadLink?:route(config('nova-export-configuration.defaults.download_route'), $fileModel->path)))
                                        ->icon('download')
                                        ->type('info')
                    );
                }
            },
        ];
    }
}
