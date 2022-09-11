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
    protected $name = '';

    /**
     * city
     *
     * @var string
     */
    protected $city = '';

    /**
     * email
     *
     * @var string
     *
     */
    protected $email = '';

    /**
     * website
     *
     * @var string
     */
    protected $website = '';

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
     * @return int $tstamp
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns the website
     *
     * @return string $website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the website
     *
     * @param string $website
     * @return void
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Returns the message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message
     *
     * @param string $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return int $hidden
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the name
     *
     * @param int $hidden
     * @return void
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }
}
