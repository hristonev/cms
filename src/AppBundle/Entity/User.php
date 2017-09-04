<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="Account already in use.", groups={"Registration"})
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(groups={"Registration", "Restore"})
     * @Assert\Email()
     * @ORM\Column(type="string", unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $roles = [];

    /**
     *
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebookId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $linkedInId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $affiliate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $province;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $postCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $restoreToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $restoreExpire;

    /**
     * @CaptchaAssert\ValidCaptcha(message = "form.validCaptcha", groups={"Registration", "Restore"})
     */
    protected $captchaCode;

    public function getUsername()
    {
        return $this->email;
    }

    public function getRoles()
    {
        array_push($this->roles, 'ROLE_GUEST');

        return array_unique($this->roles);
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        // Using BCrypt build in salt mechanism
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
        // Guarantees that the entity looks dirty to Doctrine
        // when changing the plainPassword
        $this->password = null;
    }

    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    public function getLinkedInId()
    {
        return $this->linkedInId;
    }

    public function setLinkedInId($linkedInId)
    {
        $this->linkedInId = $linkedInId;
    }

    public function getAffiliate()
    {
        return $this->affiliate;
    }

    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getProvince()
    {
        return $this->province;
    }

    public function setProvince($province)
    {
        $this->province = $province;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getPostCode()
    {
        return $this->postCode;
    }

    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return User
     */
    public function getReferFromUser()
    {
        return $this->referFromUser;
    }

    /**
     * @param User|null $referFromUser
     */
    public function setReferFromUser($referFromUser)
    {
        $this->referFromUser = $referFromUser;
    }

    /**
     * @return mixed
     */
    public function getRestoreToken()
    {
        return $this->restoreToken;
    }

    /**
     * @return mixed
     */
    public function getRestoreExpire()
    {
        return $this->restoreExpire;
    }

    /**
     * @param mixed $restoreExpire
     */
    public function setRestoreExpire($restoreExpire)
    {
        $this->restoreExpire = $restoreExpire;
    }

    /**
     * @param mixed $restoreToken
     */
    public function setRestoreToken($restoreToken)
    {
        $this->restoreToken = $restoreToken;
    }

    /**
     * @return mixed
     */
    public function getAutoGenerated()
    {
        return $this->autoGenerated;
    }

    /**
     * @param mixed $autoGenerated
     */
    public function setAutoGenerated($autoGenerated)
    {
        $this->autoGenerated = $autoGenerated;
    }

    public function getCaptchaCode()
    {
        return $this->captchaCode;
    }

    public function setCaptchaCode($captchaCode)
    {
        $this->captchaCode = $captchaCode;
    }

    /**
     * @return bool
     */
    public function isFromFacebook()
    {
        return !empty($this->getFacebookId());
    }

    public function __toString()
    {
        return (string)$this->email;
    }
}