<?php

namespace NovaExportConfiguration\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use NovaExportConfiguration\Models\ExportStoredFile;

class DownloadExportController extends Controller
{
    public function __invoke(string $file): mixed
    {
        /** @var ExportStoredFile $csvFile */
        $csvFile = ExportStoredFile::query()
            ->where('path', $file)
            ->firstOrFail();

        $storage = Storage::disk($csvFile->disk);

        abort_if(!$storage->exists($csvFile->path), 404);

        return $storage->download(
            $csvFile->path,
            preg_replace("/[^a-zA-Z0-9\_\-\.\s]/i", '_', $csvFile->name)
        );
    }
}
