<?php

namespace NovaExportConfiguration\Export;

use NovaExportConfiguration\Models\ExportStoredFile;

trait HasFileModel
{
    protected string $fileModelData = '';

    public function setFileModelData(string $fileModelData): static
    {
        $this->fileModelData = $fileModelData;

        return $this;
    }

    protected function saveFileFromModel(): ?ExportStoredFile
    {
        $fileModel = null;
        if ($this->fileModelData) {
            $fileModel = unserialize($this->fileModelData);
            if ($fileModel instanceof ExportStoredFile) {
                $fileModel->save();
            }
        }

        return $fileModel;
    }
}
