<?php

namespace NovaExportConfiguration\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaExportConfiguration\Models\ExportStoredFile;

class ExportStoredFileFactory extends Factory
{
    protected $model = ExportStoredFile::class;

    public function definition()
    {
        return [
            'type' => 'default',
            'disk' => $this->faker->word(),
            'path' => $this->faker->word(),
            'name' => $this->faker->word(),
            'meta' => [],
        ];
    }

    public function type(string $type): static
    {
        return $this->state([
            'type' => $type,
        ]);
    }
}
