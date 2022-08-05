<?php

namespace NovaExportConfiguration\Nova\Filters;

use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaExportConfiguration\NovaExportConfig;

class TypeFilter extends BooleanFilter
{
    protected string $fieldName = 'type';

    public function apply(NovaRequest $request, $query, $value)
    {
        $values = array_keys(array_filter($value));
        if (count($values) > 0) {
            return $query->whereIn($this->fieldName, $values);
        }
    }

    public function options(NovaRequest $request)
    {
        return array_flip(NovaExportConfig::typeOptions());
    }
}
