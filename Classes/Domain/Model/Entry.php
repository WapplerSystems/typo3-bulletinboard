<?php
namespace WapplerSystems\WsBulletinboard\Domain\Model;


use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
     * @var ?File
     */
    protected $image = null;

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
     * @return File|null
     */
    public function getImage(): ?File
    {
        return $this->image;
    }


}
