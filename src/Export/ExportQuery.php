<?php

namespace NovaExportConfiguration\Export;

use Illuminate\Database\Eloquent\Builder;
use NovaExportConfiguration\Models\ExportConfig;

abstract class ExportQuery
{
    protected ExportConfig $configurationModel;

    public function __construct(ExportConfig $configurationModel)
    {
        $this->configurationModel = $configurationModel;
    }

    public function getConfigurationModel(): ExportConfig
    {
        return $this->configurationModel;
    }

    protected function filters(): \NovaExportConfiguration\Casts\Json\ExportConfigFilters
    {
        return $this->configurationModel->filters;
    }

    /**
     * @return Builder
     */
    abstract public function query();
}
