<?php

namespace NovaExportConfiguration\Casts;

use JsonFieldCast\Casts\AbstractMeta;

class ExportConfigFilters extends AbstractMeta
{
    protected function metaClass(): string
    {
        return \NovaExportConfiguration\Casts\Json\ExportConfigFilters::class;
    }
}
