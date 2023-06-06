<?php

namespace NovaExportConfiguration\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use JsonFieldCast\Casts\SimpleJsonField;

/**
 * @property $meta \JsonFieldCast\Json\SimpleJsonField
 */
class ExportStoredFile extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $withoutActionEvents = true;

    protected $casts = [
        'meta' => SimpleJsonField::class,
    ];

    public function getTable(): string
    {
        return config('nova-export-configuration.tables.export_config_stored_files');
    }

    protected static function newFactory()
    {
        return \NovaExportConfiguration\Database\Factories\ExportStoredFileFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        self::deleting(function (self $model) {
            $storage = Storage::disk($model->disk);
            if ($storage->exists($model->path)) {
                $storage->delete($model->path);
            }
        });
    }

    public function downloadLink(): Attribute
    {
        return Attribute::get(fn () => route(config('nova-export-configuration.defaults.download_route'), $this->path));
    }
}
