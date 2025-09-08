<?php

namespace App\Entity\Garage;

use Doctrine\ORM\Mapping as ORM;

/**
 * PartDetails
 *
 * @ORM\Table(name="part_details", indexes={@ORM\Index(name="fk_part_estimate", columns={"estimate_of_repair_id"})})
 * @ORM\Entity
 */
class PartDetails
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
     * @ORM\Column(name="estimate_of_repair_id", type="integer", nullable=false)
     */
    private $estimateOfRepairId;

    /**
     * @var string
     *
     * @ORM\Column(name="part_name", type="string", length=255, nullable=false)
     */
    private $partName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="supplier", type="string", length=255, nullable=true)
     */
    private $supplier;

    /**
     * @var string
     *
     * @ORM\Column(name="quality", type="string", length=45, nullable=false)
     */
    private $quality;

    /**
     * @var string|null
     *
     * @ORM\Column(name="part_number", type="string", length=100, nullable=true)
     */
    private $partNumber;

    /**
     * @var float|null
     *
     * @ORM\Column(name="unit_price", type="float", precision=10, scale=0, nullable=true)
     */
    private $unitPrice = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true, options={"default"="1"})
     */
    private $quantity = 1;

    /**
     * @var float|null
     *
     * @ORM\Column(name="discount_part", type="float", precision=10, scale=0, nullable=true)
     */
    private $discountPart = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="vat_part", type="string", length=50, nullable=true, options={"default"="15"})
     */
    private $vatPart = '15';

    /**
     * @var float|null
     *
     * @ORM\Column(name="part_total", type="float", precision=10, scale=0, nullable=true)
     */
    private $partTotal;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;



    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    public function getEstimateOfRepairId()
    {
        return $this->estimateOfRepairId;
    }

    public function setEstimateOfRepairId($value)
    {
        $this->estimateOfRepairId = $value;
        return $this;
    }

    public function getPartName()
    {
        return $this->partName;
    }

    public function setPartName($value)
    {
        $this->partName = $value;
        return $this;
    }

    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier($value)
    {
        $this->supplier = $value;
        return $this;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    public function setQuality($value)
    {
        $this->quality = $value;
        return $this;
    }

    public function getPartNumber()
    {
        return $this->partNumber;
    }

    public function setPartNumber($value)
    {
        $this->partNumber = $value;
        return $this;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    public function setUnitPrice($value)
    {
        $this->unitPrice = $value;
        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($value)
    {
        $this->quantity = $value;
        return $this;
    }

    public function getDiscountPart()
    {
        return $this->discountPart;
    }

    public function setDiscountPart($value)
    {
        $this->discountPart = $value;
        return $this;
    }

    public function getVatPart()
    {
        return $this->vatPart;
    }

    public function setVatPart($value)
    {
        $this->vatPart = $value;
        return $this;
    }

    public function getPartTotal()
    {
        return $this->partTotal;
    }

    public function setPartTotal($value)
    {
        $this->partTotal = $value;
        return $this;
    }

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($value)
    {
        $this->deletedAt = $value;
        return $this;
    }

}
