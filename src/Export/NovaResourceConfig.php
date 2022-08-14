<?php

namespace NovaExportConfiguration\Export;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

abstract class NovaResourceConfig
{
    public function filterFields(NovaRequest $request, Resource $resource): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
