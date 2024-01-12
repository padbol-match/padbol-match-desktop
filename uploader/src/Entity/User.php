<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="wp2e_users")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="user_login")
     */
    private $userLogin;

    /**
     * @ORM\Column(type="string", name="user_pass")
     */
    private $userPass;

    /**
     * @ORM\Column(type="string", name="user_email")
     */
    private $userEmail;

    
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
     * Get the value of userLogin
     */ 
    public function getUserLogin()
    {
        return $this->userLogin;
    }

    /**
     * Set the value of userLogin
     *
     * @return  self
     */ 
    public function setUserLogin($userLogin)
    {
        $this->userLogin = $userLogin;

        return $this;
    }

    /**
     * Get the value of userPass
     */ 
    public function getUserPass()
    {
        return $this->userPass;
    }

    /**
     * Set the value of userPass
     *
     * @return  self
     */ 
    public function setUserPass($userPass)
    {
        $this->userPass = $userPass;

        return $this;
    }

    /**
     * Get the value of userEmail
     */ 
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * Set the value of userEmail
     *
     * @return  self
     */ 
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;

        return $this;
    }
}