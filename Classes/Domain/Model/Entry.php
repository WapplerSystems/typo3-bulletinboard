<?php
namespace WapplerSystems\WsBulletinboard\Domain\Model;


use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 *
 */
class Entry extends AbstractEntity
{

    /**
     * name
     *
     * @var string
     *
     */
    protected $title = '';


    /**
     * message
     *
     * @var string
     */
    protected $message = '';


    /**
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade remove
     */
    protected $images = null;

    /**
     * tstamp
     *
     * @var int
     */
    protected $tstamp;

    /**
     * hidden
     * @var bool
     */
    protected $hidden;


    /**
     * @var ?FrontendUser
     */
    protected $feUser = null;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @return FrontendUser|null
     */
    public function getFeUser(): ?FrontendUser
    {
        return $this->feUser;
    }

    /**
     * @return ObjectStorage
     */
    public function getImages(): ObjectStorage
    {
        return $this->images;
    }


}
