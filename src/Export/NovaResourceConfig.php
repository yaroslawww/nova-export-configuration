<?php

namespace NovaExportConfiguration\Export;

use Laravel\Nova\Http\Requests\NovaRequest;

abstract class NovaResourceConfig
{
    public function filterFields(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
