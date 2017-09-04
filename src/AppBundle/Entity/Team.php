<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 28.08.17
 * Time: 12:59
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="team")
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $shortName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $codeName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $marketValue;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $crestURL;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return mixed
     */
    public function getCodeName()
    {
        return $this->codeName;
    }

    /**
     * @param mixed $codeName
     */
    public function setCodeName($codeName)
    {
        $this->codeName = $codeName;
    }

    /**
     * @return mixed
     */
    public function getMarketValue()
    {
        return $this->marketValue;
    }

    /**
     * @param mixed $marketValue
     */
    public function setMarketValue($marketValue)
    {
        $this->marketValue = $marketValue;
    }

    /**
     * @return mixed
     */
    public function getCrestURL()
    {
        return $this->crestURL;
    }

    /**
     * @param mixed $crestURL
     */
    public function setCrestURL($crestURL)
    {
        $this->crestURL = $crestURL;
    }
}