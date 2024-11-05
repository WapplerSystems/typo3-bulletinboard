<?php

namespace WapplerSystems\WsBulletinboard\Event;

class AdjustBulletinboardSaveToDatabaseFinisherOptionsEvent
{
    public function __construct(protected array $options) {}

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
