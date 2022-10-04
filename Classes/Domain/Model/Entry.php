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
     * @var
     */
    protected $feUser;

}
