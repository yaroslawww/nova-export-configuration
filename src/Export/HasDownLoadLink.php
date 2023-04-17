<?php

namespace NovaExportConfiguration\Export;

trait HasDownLoadLink
{
    protected ?string $downloadLink = null;

    public function setDownloadLink(?string $downloadLink): static
    {
        $this->downloadLink = $downloadLink;

        return $this;
    }

    public function downloadLink(?string $default = null): ?string
    {
        return $this->downloadLink ?? $default;
    }


}
