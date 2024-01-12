<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppointmentRepository")
 * @ORM\Table(name="wp2e_bkntc_appointments")
 */
class Appointment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="staff_id")
     */
    private $staffId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="appointments")
     */
    private $service;

    /**
     * @ORM\Column(type="integer", name="starts_at")
     */
    private $startTime;

    /**
     * @ORM\Column(type="integer", name="ends_at")
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tenant", inversedBy="appointments")
     */
    private $tenant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer")
     */
    private $customer;

    public function __construct(){
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of staffId
     */ 
    public function getStaffId()
    {
        return $this->staffId;
    }

    /**
     * Set the value of staffId
     *
     * @return  self
     */ 
    public function setStaffId($staffId)
    {
        $this->staffId = $staffId;

        return $this;
    }

    /**
     * Get the value of service
     */ 
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set the value of service
     *
     * @return  self
     */ 
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the value of startTime
     */ 
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set the value of startTime
     *
     * @return  self
     */ 
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get the value of duration
     */ 
    public function getDuration()
    {
        return (($this->endTime - $this->startTime) / 60);
    }


    /**
     * Get the value of tenant
     */ 
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Set the value of tenant
     *
     * @return  self
     */ 
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Get the value of endTime
     */ 
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set the value of endTime
     *
     * @return  self
     */ 
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

     /**
     * Get the value of customer
     */ 
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set the value of customer
     *
     * @return  self
     */ 
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }
}