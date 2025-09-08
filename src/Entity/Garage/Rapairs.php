<?php

namespace App\Entity\Garage;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rapairs
 *
 * @ORM\Table(name="rapairs")
 * @ORM\Entity
 */
class Rapairs
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="claim_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $claimId;

    /**
     * @var int
     *
     * @ORM\Column(name="garage_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $garageId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="appointement_date", type="date", nullable=true)
     */
    private $appointementDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="appointement_time", type="time", nullable=true)
     */
    private $appointementTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="swan_claim_handler", type="string", length=150, nullable=true)
     */
    private $swanClaimHandler;

    /**
     * @var string|null
     *
     * @ORM\Column(name="remarks", type="text", length=65535, nullable=true)
     */
    private $remarks;

    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $statusId;



    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    public function getClaimId()
    {
        return $this->claimId;
    }

    public function setClaimId($value)
    {
        $this->claimId = $value;
        return $this;
    }

    public function getGarageId()
    {
        return $this->garageId;
    }

    public function setGarageId($value)
    {
        $this->garageId = $value;
        return $this;
    }

    public function getAppointementDate()
    {
        return $this->appointementDate;
    }

    public function setAppointementDate($value)
    {
        $this->appointementDate = $value;
        return $this;
    }

    public function getAppointementTime()
    {
        return $this->appointementTime;
    }

    public function setAppointementTime($value)
    {
        $this->appointementTime = $value;
        return $this;
    }

    public function getSwanClaimHandler()
    {
        return $this->swanClaimHandler;
    }

    public function setSwanClaimHandler($value)
    {
        $this->swanClaimHandler = $value;
        return $this;
    }

    public function getRemarks()
    {
        return $this->remarks;
    }

    public function setRemarks($value)
    {
        $this->remarks = $value;
        return $this;
    }

    public function getStatusId()
    {
        return $this->statusId;
    }

    public function setStatusId($value)
    {
        $this->statusId = $value;
        return $this;
    }

}
