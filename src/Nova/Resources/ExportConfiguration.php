<?php

namespace NovaExportConfiguration\Nova\Resources;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource;
use NovaExportConfiguration\Nova\Actions\ConfiguredExportToExcelAction;
use NovaExportConfiguration\Nova\Actions\RegenerateExportResultAction;
use NovaExportConfiguration\Nova\Filters\TypeFilter;
use NovaExportConfiguration\NovaExportConfig;
use NovaExportConfiguration\Repositories\ExportRepository;

/**
 * @extends Resource<\NovaExportConfiguration\Models\ExportConfig>
 */
class ExportConfiguration extends Resource
{
    /**
     * The model the resource corresponds to.
     * Override using service provider.
     *
     * @var class-string<\NovaExportConfiguration\Models\ExportConfig>
     */
    public static $model;

    public static $group = 'Export';

    public static $title = 'name';

    public static $search = [
        'name',
    ];

    public function fields(NovaRequest $request)
    {
        $fields = [
            ID::make(__('ID'), 'id')->sortable(),
            Select::make(__('Type'), 'type')
                ->onlyOnForms()
                ->hideWhenUpdating()
                ->required()
                ->searchable()
                ->options(NovaExportConfig::typeOptions()),
            Text::make(__('Type'), 'type')
                ->displayUsing(fn ($val, $model) => NovaExportConfig::getRepositories()->getByName($model?->type)?->label())
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->sortable(),
            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules([
                    'required',
                    'max:255',
                ]),
            Textarea::make(__('Description'), 'description')
                ->hideFromIndex()
                ->alwaysShow()
                ->rules([
                    'nullable',
                    'max:3000',
                ]),
            Text::make(__('Description'), 'description')
                ->displayUsing(fn ($val) => nl2br($val))
                ->asHtml()
                ->onlyOnIndex(),
            new Panel(__('Filters'), $this->filterFields($request)),
        ];

        return $fields;
    }

    protected function filterFields(NovaRequest $request): array
    {
        return ($repo = $this->getRepo($request)) ? $repo->novaResourceConfig()->filterFields($request, $this) : [];
    }

    protected function getRepo(NovaRequest $request): ?ExportRepository
    {
        $type = $this->model()?->type;
        if (!$type) {
            if ($request->viaResource()) {
                $type = $request->viaResource()::find($request->viaResourceId)?->type;
            } else {
                $type = $request->findModel()?->type;
            }
        }

        return NovaExportConfig::getRepositories()->getByName($type);
    }

    public function filters(NovaRequest $request)
    {
        return [
            new TypeFilter(),
        ];
    }

    public function actions(NovaRequest $request)
    {
        return array_merge([
            (new ConfiguredExportToExcelAction())->askForFilename()
                ->askForWriterType(),
            new RegenerateExportResultAction,
        ], NovaExportConfig::configurationActions($request));
    }
}
