<?php

namespace NovaExportConfiguration\Export;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

abstract class ConfiguredExport implements FromQuery, WithMapping, WithEvents, WithHeadings, WithCustomChunkSize
{
    use Exportable, HasFileModel, WithNotification;

    protected ExportQuery $exportQuery;

    protected int $chunkSize = 500;

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

    public function setChunkSize(int $chunkSize): static
    {
        $this->chunkSize = $chunkSize;

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

                $fileModel = $this->saveFileFromModel();
                if($fileModel?->exists) {
                    $this->notifyUser($fileModel);
                }
            },
        ];
    }
}
