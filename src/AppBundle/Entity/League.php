<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 16.08.17
 * Time: 14:51
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="league")
 */
class League implements Translatable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Season")
     */
    private $season;

    /**
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", nullable=true)
     */
    private $caption;

    /**
     * @Gedmo\Translatable()
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $currentMatchday;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfMatchdays;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfTeams;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfGames;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lasUpdate;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Season
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @param Season $season
     */
    public function setSeason($season)
    {
        $this->season = $season;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param mixed $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCurrentMatchday()
    {
        return $this->currentMatchday;
    }

    /**
     * @param mixed $currentMatchday
     */
    public function setCurrentMatchday($currentMatchday)
    {
        $this->currentMatchday = $currentMatchday;
    }

    /**
     * @return mixed
     */
    public function getNumberOfMatchdays()
    {
        return $this->numberOfMatchdays;
    }

    /**
     * @param mixed $numberOfMatchdays
     */
    public function setNumberOfMatchdays($numberOfMatchdays)
    {
        $this->numberOfMatchdays = $numberOfMatchdays;
    }

    /**
     * @return mixed
     */
    public function getNumberOfTeams()
    {
        return $this->numberOfTeams;
    }

    /**
     * @param mixed $numberOfTeams
     */
    public function setNumberOfTeams($numberOfTeams)
    {
        $this->numberOfTeams = $numberOfTeams;
    }

    /**
     * @return mixed
     */
    public function getNumberOfGames()
    {
        return $this->numberOfGames;
    }

    /**
     * @param mixed $numberOfGames
     */
    public function setNumberOfGames($numberOfGames)
    {
        $this->numberOfGames = $numberOfGames;
    }

    /**
     * @return \DateTime
     */
    public function getLasUpdate()
    {
        return $this->lasUpdate;
    }

    /**
     * @param \DateTime $lasUpdate
     */
    public function setLasUpdate($lasUpdate)
    {
        $this->lasUpdate = $lasUpdate;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function __toString()
    {
        return '['. $this->code. ']'. $this->caption;
    }
}