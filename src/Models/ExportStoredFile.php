<?php

namespace NovaExportConfiguration\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use JsonFieldCast\Casts\SimpleJsonField;
use NovaExportConfiguration\Database\Factories\ExportStoredFileFactory;

/**
 * @property string $type
 * @property string $disk
 * @property string $path
 * @property string $name
 * @property \JsonFieldCast\Json\SimpleJsonField $meta
 * @property-read string $download_link
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

    protected static function boot(): void
    {
        parent::boot();

        self::deleting(function (self $model) {
            $storage = Storage::disk($model->disk);
            if ($storage->exists($model->path)) {
                $storage->delete($model->path);
            }
        });
    }

    public static function init(string $type, string $disk, string $path, string $name, ?\Closure $tap = null): static
    {
        $model       = new static();
        $model->type = $type;
        $model->disk = $disk;
        $model->path = $path;
        $model->name = $name;

        if(is_callable($tap)) {
            $tap($model);
        }

        return $model;
    }

    public function downloadLink(): Attribute
    {
        return Attribute::get(fn () => route(config('nova-export-configuration.defaults.download_route'), $this->path));
    }

    protected static function newFactory(): ExportStoredFileFactory
    {
        return ExportStoredFileFactory::new();
    }
}
