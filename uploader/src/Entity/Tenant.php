<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TenantRepository")
 * @ORM\Table(name="wp2e_bkntc_tenants")
 */
class Tenant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="users")
     */
    private $user;

    /**
     * @ORM\Column(type="string", name="full_name")
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", name="domain")
     */
    private $domain;

    /**
     * @ORM\Column(type="string", name="activation_token")
     */
    private $activationToken;

    /**
     * @ORM\Column(type="string", name="email")
     */
    private $email;

    
    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Get the value of user
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */ 
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of fullName
     */ 
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set the value of fullName
     *
     * @return  self
     */ 
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get the value of domain
     */ 
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the value of domain
     *
     * @return  self
     */ 
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get the value of activationToken
     */ 
    public function getActivationToken()
    {
        return $this->activationToken;
    }

    /**
     * Set the value of activationToken
     *
     * @return  self
     */ 
    public function setActivationToken($activationToken)
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }
}