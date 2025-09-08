<?php

namespace App\Entity\Garage;

use Doctrine\ORM\Mapping as ORM;

/**
 * RepairParts
 *
 * @ORM\Table(name="repair_parts", indexes={@ORM\Index(name="fk_repair_parts_rapairs1_idx", columns={"rapairs_id"})})
 * @ORM\Entity
 */
class RepairParts
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
     * @ORM\Column(name="part_details_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $partDetailsId;

    /**
     * @var int
     *
     * @ORM\Column(name="users_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $usersId;

    /**
     * @var \Rapairs
     *
     * @ORM\ManyToOne(targetEntity="Rapairs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rapairs_id", referencedColumnName="id")
     * })
     */
    private $rapairs;



    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    public function getPartDetailsId()
    {
        return $this->partDetailsId;
    }

    public function setPartDetailsId($value)
    {
        $this->partDetailsId = $value;
        return $this;
    }

    public function getUsersId()
    {
        return $this->usersId;
    }

    public function setUsersId($value)
    {
        $this->usersId = $value;
        return $this;
    }

    public function getRapairs()
    {
        return $this->rapairs;
    }

    public function setRapairs($value)
    {
        $this->rapairs = $value;
        return $this;
    }

}
