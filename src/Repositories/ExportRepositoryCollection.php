<?php

namespace NovaExportConfiguration\Repositories;

use Illuminate\Support\Collection;

class ExportRepositoryCollection extends Collection
{
    public function getByName(?string $name): ?ExportRepository
    {
        return $this->first(fn($item) => $item->name() === $name);
    }

    public function uniqueNames(): ExportRepositoryCollection
    {
        return $this->unique(fn($item) => $item->name());
    }

}
