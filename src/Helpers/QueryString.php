<?php

namespace NovaExportConfiguration\Helpers;

class QueryString
{
    public static function readableSqlQuery($query): string
    {
        return \Illuminate\Support\Str::replaceArray(
            '?',
            collect($query->getBindings())
                ->map(function ($i) {
                    if (is_object($i)) {
                        $i = (string) $i;
                    }

                    return (is_string($i)) ? "'$i'" : $i;
                })->all(),
            $query->toSql()
        );
    }
}
