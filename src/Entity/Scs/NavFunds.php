<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\DashboardViewController;
use Doctrine\ORM\Mapping as ORM;

/**
 * NavFunds
 *
 * @ORM\Table(name="nav_funds")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=App\Repository\NavFundsRepository::class)
 */
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/nav-funds',
            controller: DashboardViewController::class . '::getNavOfTheFunds',
            // parameters: [ 
            //     'id' => new QueryParameter()
            // ]
        ),    
    ],
)]
class NavFunds
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
     * @ORM\Column(name="code_name", type="string", length=45, nullable=false)
     */
    private $codeName;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=45, nullable=false)
     */
    private $typeNav;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=10, scale=0, nullable=false)
     */
    private $value;

    /**
     * @var \Fund
     *
     * @ORM\ManyToOne(targetEntity="Fund")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fund_id", referencedColumnName="id")
     * })
     */
    private $fundId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nav_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $navDate = 'CURRENT_TIMESTAMP';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeName(): ?string
    {
        return $this->codeName;
    }

    public function setCodeName(string $codeName): static
    {
        $this->codeName = $codeName;

        return $this;
    }

    public function getTypeNav(): ?string
    {
        return $this->typeNav;
    }

    public function setTypeNav(string $typeNav): static
    {
        $this->typeNav = $typeNav;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

        return $this;
    }

     public function getNavDate(): ?\DateTime
    {
        return $this->navDate;
    }

    public function setNavDate(\DateTime $navDate): static
    {
        $this->navDate = $navDate;

        return $this;
    }

     public function getFundId(): ?Fund
    {
        return $this->fundId;
    }

    public function setFundId(?Fund $fundId): static
    {
        $this->fundId = $fundId;

        return $this;
    }



}
