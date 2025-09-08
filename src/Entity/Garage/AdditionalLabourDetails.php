<?php

namespace App\Entity\Garage;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdditionalLabourDetails
 *
 * @ORM\Table(name="additional_labour_details")
 * @ORM\Entity
 */
class AdditionalLabourDetails
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
     * @var int
     *
     * @ORM\Column(name="estimate_of_repairs_id", type="integer", nullable=false)
     */
    private $estimateOfRepairsId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="eor_or_surveyor", type="string", length=50, nullable=true)
     */
    private $eorOrSurveyor;

    /**
     * @var float|null
     *
     * @ORM\Column(name="painting_cost", type="float", precision=10, scale=0, nullable=true)
     */
    private $paintingCost;

    /**
     * @var float|null
     *
     * @ORM\Column(name="painting_materiels", type="float", precision=10, scale=0, nullable=true)
     */
    private $paintingMateriels;

    /**
     * @var float|null
     *
     * @ORM\Column(name="sundries", type="float", precision=10, scale=0, nullable=true)
     */
    private $sundries;

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_of_repaire_days", type="integer", nullable=true)
     */
    private $numOfRepaireDays;

    /**
     * @var float|null
     *
     * @ORM\Column(name="discount_add_labour", type="float", precision=10, scale=0, nullable=true)
     */
    private $discountAddLabour;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vat", type="string", length=50, nullable=true)
     */
    private $vat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="add_labour_total", type="float", precision=10, scale=0, nullable=true)
     */
    private $addLabourTotal;



    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    public function getEstimateOfRepairsId()
    {
        return $this->estimateOfRepairsId;
    }

    public function setEstimateOfRepairsId($value)
    {
        $this->estimateOfRepairsId = $value;
        return $this;
    }

    public function getEorOrSurveyor()
    {
        return $this->eorOrSurveyor;
    }

    public function setEorOrSurveyor($value)
    {
        $this->eorOrSurveyor = $value;
        return $this;
    }

    public function getPaintingCost()
    {
        return $this->paintingCost;
    }

    public function setPaintingCost($value)
    {
        $this->paintingCost = $value;
        return $this;
    }

    public function getPaintingMateriels()
    {
        return $this->paintingMateriels;
    }

    public function setPaintingMateriels($value)
    {
        $this->paintingMateriels = $value;
        return $this;
    }

    public function getSundries()
    {
        return $this->sundries;
    }

    public function setSundries($value)
    {
        $this->sundries = $value;
        return $this;
    }

    public function getNumOfRepaireDays()
    {
        return $this->numOfRepaireDays;
    }

    public function setNumOfRepaireDays($value)
    {
        $this->numOfRepaireDays = $value;
        return $this;
    }

    public function getDiscountAddLabour()
    {
        return $this->discountAddLabour;
    }

    public function setDiscountAddLabour($value)
    {
        $this->discountAddLabour = $value;
        return $this;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function setVat($value)
    {
        $this->vat = $value;
        return $this;
    }

    public function getAddLabourTotal()
    {
        return $this->addLabourTotal;
    }

    public function setAddLabourTotal($value)
    {
        $this->addLabourTotal = $value;
        return $this;
    }

}
