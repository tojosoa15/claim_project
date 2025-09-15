<?php

namespace App\Entity\ClaimUser;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProfessionalInformations
 *
 * @ORM\Table(name="professional_informations", uniqueConstraints={@ORM\UniqueConstraint(name="users_id_UNIQUE", columns={"users_id"})})
 * @ORM\Entity
 */
class EmploymentInformation
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
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    private $users;

    /**
     * @var string
     *
     * @ORM\Column(name="present_occupation", type="string", length=150, nullable=false)
     */
    private $presentOccupation;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=150, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="company_address", type="string", length=50, nullable=false)
     */
    private $companyAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="office_phone", type="string", length=50, nullable=false)
     */
    private $officePhone;

    /**
     * @var string
     *
     * @ORM\Column(name="monthly_income", type="string", length=50, nullable=false)
     */
    private $monthlyIncome;

    // -------------------------
    // Getters & Setters
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): static
    {
        $this->users = $users;
        return $this;
    }

    public function getPresentOccupation(): ?string
    {
        return $this->presentOccupation;
    }

    public function setPresentOccupation(string $presentOccupation): static
    {
        $this->presentOccupation = $presentOccupation;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;
        return $this;
    }

    public function getOfficePhone(): ?string
    {
        return $this->officePhone;
    }

    public function setOfficePhone(string $officePhone): static
    {
        $this->officePhone = $officePhone;
        return $this;
    }

    public function getMonthlyIncome(): ?string
    {
        return $this->monthlyIncome;
    }

    public function setMonthlyIncome(string $monthlyIncome): static
    {
        $this->monthlyIncome = $monthlyIncome;
        return $this;
    }
}
