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

    protected function getQueue(?string $default = null): ?string
    {
        return $this->queueName ?: $default;
    }
}
