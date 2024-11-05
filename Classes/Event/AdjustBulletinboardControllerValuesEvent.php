<?php

namespace WapplerSystems\WsBulletinboard\Event;

class AdjustBulletinboardControllerValuesEvent
{
  public function __construct(protected array $assignedValues)
  {
  }

  public function getAssignedValues(): array
  {
    return $this->assignedValues;
  }

  public function setAssignedValues(array $assignedValues): void
  {
    $this->assignedValues = $assignedValues;
  }
}
