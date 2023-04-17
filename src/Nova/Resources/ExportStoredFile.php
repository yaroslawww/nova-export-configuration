<?php

namespace NovaExportConfiguration\Nova\Resources;

use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use NovaExportConfiguration\Nova\Actions\CustomFileExports;
use NovaExportConfiguration\NovaExportConfig;

class ExportStoredFile extends Resource
{
    public static $model = \NovaExportConfiguration\Models\ExportStoredFile::class;

    public static $title = 'name';

    public static $group = 'Export';

    public static $search = [
        'name',
    ];

    public static function label()
    {
        return __('Exported Files');
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make(__('ID'), 'id')
              ->hideFromIndex()
              ->sortable(),

            Text::make(__('File Name'), 'name')
                ->sortable()
                ->showOnDetail()
                ->showOnIndex(),

            Text::make(__('Type'), 'disk')
                ->exceptOnForms(),

            DateTime::make(__('Created At'), 'created_at')
                    ->sortable(),

            Text::make(__('Download Link'), function () {
                return view('nova-export-configuration::link', [
                    'path' => $this->path,
                ])->render();
            })->asHtml()->hideWhenCreating()->hideWhenUpdating(),
        ];
    }

    public function actions(NovaRequest $request)
    {
        $actions = [];

        if(!empty($exportsList = NovaExportConfig::customExportsOptions())) {
            $actions[] = CustomFileExports::make()
                ->exportsList($exportsList)
                ->askForFilename()
                ->askForWriterType();
        }

        return $actions;
    }
}
