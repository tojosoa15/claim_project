<?php

namespace App\Entity\ClaimUser;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * FinancialInformations
 *
 * @ORM\Table(name="financial_informations", uniqueConstraints={@ORM\UniqueConstraint(name="users_id_UNIQUE", columns={"users_id"})})
 * @ORM\Entity
 */
class FinancialInformations
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
     * @ORM\Column(name="vat_number", type="string", length=255, nullable=false)
     */
    private $vatNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_identification_number", type="string", length=255, nullable=false)
     */
    private $taxIdentificationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=150, nullable=false)
     */
    private $bankName;

    /**
     * @var int
     *
     * @ORM\Column(name="bank_account_number", type="bigint", nullable=false)
     */
    private $bankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="swift_code", type="string", length=255, nullable=false)
     */
    private $swiftCode;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_holder_name", type="string", length=50, nullable=false)
     */
    private $bankHolderName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_address", type="string", length=50, nullable=false)
     */
    private $bankAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_country", type="string", length=50, nullable=false)
     */
    private $bankCountry;
    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    private $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function getTaxIdentificationNumber(): ?string
    {
        return $this->taxIdentificationNumber;
    }

    public function setTaxIdentificationNumber(string $taxIdentificationNumber): static
    {
        $this->taxIdentificationNumber = $taxIdentificationNumber;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): static
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(string $bankAccountNumber): static
    {
        $this->bankAccountNumber = $bankAccountNumber;

        return $this;
    }

    public function getSwiftCode(): ?string
    {
        return $this->swiftCode;
    }

    public function setSwiftCode(string $swiftCode): static
    {
        $this->swiftCode = $swiftCode;

        return $this;
    }

    public function getBankHolderName(): ?string
    {
        return $this->bankHolderName;
    }

    public function setBankHolderName(string $bankHolderName): static
    {
        $this->bankHolderName = $bankHolderName;

        return $this;
    }

    public function getBankAddress(): ?string
    {
        return $this->bankAddress;
    }

    public function setBankAddress(string $bankAddress): static
    {
        $this->bankAddress = $bankAddress;

        return $this;
    }

    public function getBankCountry(): ?string
    {
        return $this->bankCountry;
    }

    public function setBankCountry(string $bankCountry): static
    {
        $this->bankCountry = $bankCountry;

        return $this;
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


}
