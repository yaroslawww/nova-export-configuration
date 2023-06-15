<?php

namespace NovaExportConfiguration\Export;

use NovaExportConfiguration\Models\ExportStoredFile;

trait HasFileModel
{
    protected string $fileModelData = '';

    public function useStoreFile(ExportStoredFile $file): static
    {
        return $this->setFileModelData(serialize($file));
    }

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
