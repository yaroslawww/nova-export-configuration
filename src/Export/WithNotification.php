<?php

namespace NovaExportConfiguration\Export;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;
use NovaExportConfiguration\Models\ExportStoredFile;

trait WithNotification
{
    use HasDownLoadLink;

    protected ?string $notificationUserClass = null;
    protected ?int $notificationUserId       = null;

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

    public function notifyUser(ExportStoredFile $fileModel): void
    {
        $user = $this->notificationUser();
        if ($user && $fileModel?->exists) {
            $user->notify(
                NovaNotification::make()
                    ->message("File {$fileModel->name} exported")
                    ->action('Download', URL::remote($this->downloadLink(route(config('nova-export-configuration.defaults.download_route'), $fileModel->path))))
                    ->icon('download')
                    ->type('info')
            );
        }
    }
}
