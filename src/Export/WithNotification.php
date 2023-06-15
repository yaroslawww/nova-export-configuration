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

    /**
     * Set user data to end notifications for.
     *
     * @param Model|null $notificationUser
     * @return $this
     */
    public function setNotificationUser(?Model $notificationUser = null): static
    {
        $this->notificationUserId    = $notificationUser?->getKey();
        $this->notificationUserClass = $notificationUser?->getMorphClass();

        return $this;
    }

    /**
     * Find user to send notification.
     *
     * @return Model|null
     */
    public function notificationUser(): ?Model
    {
        if ($this->notificationUserClass && $this->notificationUserId) {
            $class = Relation::getMorphedModel($this->notificationUserClass);
            if ($class && is_a($class, Model::class, true)) {
                return $class::query()->find($this->notificationUserId);
            }
        }

        return null;
    }


    /**
     * Send notification to user.
     *
     * @param ExportStoredFile $fileModel
     * @return void
     */
    public function notifyUser(ExportStoredFile $fileModel): void
    {
        $user = $this->notificationUser();
        if ($user && $fileModel?->exists) {
            $user->notify(
                NovaNotification::make()
                    ->message("File {$fileModel->name} exported")
                    ->action('Download', URL::remote($this->downloadLink($fileModel->download_link)))
                    ->icon('download')
                    ->type('info')
            );
        }
    }
}
