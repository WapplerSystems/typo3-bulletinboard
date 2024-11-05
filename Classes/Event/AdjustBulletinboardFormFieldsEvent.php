<?php

namespace WapplerSystems\WsBulletinboard\Event;

use TYPO3\CMS\Form\Domain\Model\FormElements\Section;
use WapplerSystems\WsBulletinboard\Domain\Model\Entry;

class AdjustBulletinboardFormFieldsEvent
{
    protected ?Entry $entry;

    public function __construct(protected Section $fieldset, protected array $configuration, ?Entry $entry = null)
    {
        $this->entry = $entry;
    }

    public function getFieldset(): Section
    {
        return $this->fieldset;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }
}
