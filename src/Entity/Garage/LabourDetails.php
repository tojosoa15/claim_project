<?php

namespace App\Entity\Garage;

use Doctrine\ORM\Mapping as ORM;

/**
 * LabourDetails
 *
 * @ORM\Table(name="labour_details", indexes={@ORM\Index(name="fk_labour_part", columns={"part_detail_id"})})
 * @ORM\Entity
 */
class LabourDetails
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
     * @ORM\Column(name="part_detail_id", type="integer", nullable=false)
     */
    private $partDetailId;

    /**
     * @var string
     *
     * @ORM\Column(name="eor_or_surveyor", type="string", length=50, nullable=false)
     */
    private $eorOrSurveyor;

    /**
     * @var string
     *
     * @ORM\Column(name="activity", type="string", length=255, nullable=false)
     */
    private $activity;

    /**
     * @var int|null
     *
     * @ORM\Column(name="number_of_hours", type="integer", nullable=true)
     */
    private $numberOfHours = '0';

    /**
     * @var float|null
     *
     * @ORM\Column(name="discount_labour", type="float", precision=10, scale=0, nullable=true)
     */
    private $discountLabour = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="vat_labour", type="string", length=50, nullable=true, options={"default"="15"})
     */
    private $vatLabour = '15';

    /**
     * @var float|null
     *
     * @ORM\Column(name="hourly_cost_labour", type="float", precision=10, scale=0, nullable=true)
     */
    private $hourlyCostLabour = '0';

    /**
     * @var float|null
     *
     * @ORM\Column(name="labour_total", type="float", precision=10, scale=0, nullable=true)
     */
    private $labourTotal;



    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    public function getPartDetailId()
    {
        return $this->partDetailId;
    }

    public function setPartDetailId($value)
    {
        $this->partDetailId = $value;
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

    public function getActivity()
    {
        return $this->activity;
    }

    public function setActivity($value)
    {
        $this->activity = $value;
        return $this;
    }

    public function getNumberOfHours()
    {
        return $this->numberOfHours;
    }

    public function setNumberOfHours($value)
    {
        $this->numberOfHours = $value;
        return $this;
    }

    public function getDiscountLabour()
    {
        return $this->discountLabour;
    }

    public function setDiscountLabour($value)
    {
        $this->discountLabour = $value;
        return $this;
    }

    public function getVatLabour()
    {
        return $this->vatLabour;
    }

    public function setVatLabour($value)
    {
        $this->vatLabour = $value;
        return $this;
    }

    public function getHourlyCostLabour()
    {
        return $this->hourlyCostLabour;
    }

    public function setHourlyCostLabour($value)
    {
        $this->hourlyCostLabour = $value;
        return $this;
    }

    public function getLabourTotal()
    {
        return $this->labourTotal;
    }

    public function setLabourTotal($value)
    {
        $this->labourTotal = $value;
        return $this;
    }

}
