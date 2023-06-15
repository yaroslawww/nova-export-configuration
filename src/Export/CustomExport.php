<?php

namespace NovaExportConfiguration\Export;

use Laravel\Nova\Nova;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Export class, allow developer to extend configuration
 * and use it in nova admin as action to call.
 */
abstract class CustomExport implements WithEvents
{
    use Exportable, HasFileModel, WithNotification;

    public static function key(): string
    {
        return class_basename(static::class);
    }

    public static function name(): string
    {
        return Nova::humanize(class_basename(static::class));
    }

    public static function queueName(): ?string
    {
        return config('nova-export-configuration.defaults.queue');
    }

    public static function diskName(): ?string
    {
        return config('nova-export-configuration.defaults.disk');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $fileModel = $this->saveFileFromModel();
                if($fileModel?->exists) {
                    $this->notifyUser($fileModel);
                }
            },
        ];
    }
}
