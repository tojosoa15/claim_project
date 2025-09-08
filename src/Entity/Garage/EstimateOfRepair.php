<?php

namespace App\Entity\Garage;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstimateOfRepair
 *
 * @ORM\Table(name="estimate_of_repair")
 * @ORM\Entity
 */
class EstimateOfRepair
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="claim_number", type="string", length=100, nullable=false)
     */
    private $claimNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="garage_id", type="integer", nullable=false)
     */
    private $garageId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="remarks", type="text", length=65535, nullable=true)
     */
    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="current_step", type="string", length=50, nullable=false)
     */
    private $currentStep;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';



    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    public function getClaimNumber()
    {
        return $this->claimNumber;
    }

    public function setClaimNumber($value)
    {
        $this->claimNumber = $value;
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

    public function getRemarks()
    {
        return $this->remarks;
    }

    public function setRemarks($value)
    {
        $this->remarks = $value;
        return $this;
    }

    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    public function setCurrentStep($value)
    {
        $this->currentStep = $value;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($value)
    {
        $this->updatedAt = $value;
        return $this;
    }

}
