<?php

namespace NovaExportConfiguration\Repositories;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, ExportRepository>
 */
class ExportRepositoryCollection extends Collection
{
    public function getByName(?string $name): ?ExportRepository
    {
        return $this->first(fn ($item) => $item->name() === $name);
    }

    public function uniqueNames(): ExportRepositoryCollection
    {
        return $this->unique(fn ($item) => $item->name());
    }
}
