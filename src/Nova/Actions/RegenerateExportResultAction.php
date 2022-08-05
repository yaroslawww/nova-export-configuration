<?php

namespace NovaExportConfiguration\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaExportConfiguration\Models\ExportConfig;

class RegenerateExportResultAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnIndex = false;

    public $showInline = true;

    public $showOnDetail = true;

    public function name()
    {
        return __('Regenerate result');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            if ($model instanceof ExportConfig) {
                $model->updateConfigurationContent(true);

                return Action::message(__('Data sent to queue, please wait 5-10 mins.'));
            }
        }

        return Action::danger(__('No models found.'));
    }

    public function fields(NovaRequest $request)
    {
        return [];
    }
}
