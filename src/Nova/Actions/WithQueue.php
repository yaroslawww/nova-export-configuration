<?php

namespace NovaExportConfiguration\Nova\Actions;

trait WithQueue
{
    protected ?string $queueName = null;

    public function withQueue(?string $queueName = null): static
    {
        $this->queueName = $queueName;

        return $this;
    }

    protected function getDisk(): ?string
    {
        return $this->queueName;
    }
}
