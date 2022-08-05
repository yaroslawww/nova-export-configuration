<?php

namespace NovaExportConfiguration\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaExportConfiguration\Models\ExportConfig;

class ExportConfigFactory extends Factory
{
    protected $model = ExportConfig::class;

    public function definition()
    {
        return [
            'type'           => 'default',
            'name'           => $this->faker->unique()->word(),
            'description'    => $this->faker->sentence(),
            'meta'           => [],
            'sql_query'      => null,
            'last_export_at' => null,
        ];
    }

    public function type(string $type): static
    {
        return $this->state([
            'type' => $type,
        ]);
    }

    public function name(string $name): static
    {
        return $this->state([
            'name' => $name,
        ]);
    }
}
