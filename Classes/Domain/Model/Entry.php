<?php

namespace WapplerSystems\WsBulletinboard\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Entry extends AbstractEntity
{
    protected string $title = '';
    protected string $message = '';

    /**
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Cascade(['value' => 'remove'])] // remove
    protected $images = null;

    protected int $tstamp;
    protected int $endtime = 0;
    protected bool $hidden;
    protected ?FrontendUser $feUser = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function getEndTime(): int
    {
        return $this->endtime;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getFeUser(): ?FrontendUser
    {
        return $this->feUser;
    }

    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
