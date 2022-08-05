<?php

namespace NovaExportConfiguration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JsonFieldCast\Casts\SimpleJsonField;
use NovaExportConfiguration\Casts\ExportConfigFilters;
use NovaExportConfiguration\Helpers\QueryString;
use NovaExportConfiguration\NovaExportConfig;
use NovaExportConfiguration\Repositories\ExportRepository;

/**
 * @property \NovaExportConfiguration\Casts\Json\ExportConfigFilters $filters
 * @property \JsonFieldCast\Json\SimpleJsonField $meta
 */
class ExportConfig extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'sql_query',
    ];

    protected $casts = [
        'last_export_at' => 'datetime',
        'filters'        => ExportConfigFilters::class,
        'meta'           => SimpleJsonField::class,
    ];

    public function getTable()
    {
        return config('nova-export-configuration.tables.export_configs');
    }

    protected static function newFactory()
    {
        return \NovaExportConfiguration\Database\Factories\ExportConfigFactory::new();
    }

    protected static function booted()
    {
        static::saving(function (self $model) {
            if ($model->isDirty('filters')) {
                $model->updateConfigurationContent();
            }
        });
        static::created(fn (self $model) => $model->updateConfigurationContent());
    }

    public function exportRepository(): ?ExportRepository
    {
        return NovaExportConfig::getRepositories()->getByName($this->attributes['type']??null);
    }

    public function updateConfigurationContent(bool $persist = false)
    {
        if ($repo = $this->exportRepository()) {
            $this->fill([
                'sql_query' => QueryString::readableSqlQuery($repo->exportQuery($this)->query()),
            ]);
            $repo->regenerateConfigurationData($this, $persist);

            if ($persist) {
                $this->save();
            }
        }
    }

    public function setAttribute($key, $value)
    {
        if (($repo = $this->exportRepository()) && $repo->isFilterKey($key)) {
            return $repo->modelSetAttribute($this, $key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        if (($repo = $this->exportRepository()) && $repo->isFilterKey($key)) {
            return $repo->modelGetAttribute($this, $key);
        }

        return parent::getAttribute($key);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
