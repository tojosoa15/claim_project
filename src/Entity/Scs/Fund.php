<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\DashboardViewController;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fund
 *
 * @ORM\Table(name="fund")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=App\Repository\FundRepository::class)
 */
#[ApiResource(
    operations: [
        // List of funds with performance
        new GetCollection(
            uriTemplate: '/api/funds',
            controller: DashboardViewController::class . '::getListFundsPerformance',
            parameters: [ 
                'userId'    => new QueryParameter(),
                'fundName'  => new QueryParameter(),
                'period'    => new QueryParameter()
            ]
        ),    
        // Nav and last valuation date
        new Get(
            uriTemplate: '/api/last-valuation-date',
            controller: DashboardViewController::class . '::getNavLastValuationDate',
            parameters: [ 
                'userId'    => new QueryParameter(),
                'sortBy'    => new QueryParameter()
            ]
        )
    ],
)]
class Fund
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
     * @ORM\Column(name="reference", type="string", length=50, nullable=false)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="fund_name", type="string", length=250, nullable=false)
     */
    private $fundName;

    /**
     * @var float
     *
     * @ORM\Column(name="no_of_shares", type="float", precision=10, scale=0, nullable=false)
     */
    private $noOfShares;

    /**
     * @var string
     *
     * @ORM\Column(name="nav", type="string", length=100, nullable=false)
     */
    private $nav;

    /**
     * @var string
     *
     * @ORM\Column(name="total_amount_ccy", type="string", length=100, nullable=false)
     */
    private $totalAmountCcy;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount_mur", type="float", precision=10, scale=0, nullable=false)
     */
    private $totalAmountMur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fund_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $fundDate = 'CURRENT_TIMESTAMP';

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;


    // /**
    //  * @var int
    //  *
    //  * @ORM\Column(name="nav_id", type="integer", nullable=false)
    //  */
    // private $navId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getFundName(): ?string
    {
        return $this->fundName;
    }

    public function setFundName(string $fundName): static
    {
        $this->fundName = $fundName;

        return $this;
    }

    public function getNoOfShares(): ?float
    {
        return $this->noOfShares;
    }

    public function setNoOfShares(float $noOfShares): static
    {
        $this->noOfShares = $noOfShares;

        return $this;
    }

    public function getNav(): ?string
    {
        return $this->nav;
    }

    public function setNav(string $nav): static
    {
        $this->nav = $nav;

        return $this;
    }

    public function getTotalAmountCcy(): ?string
    {
        return $this->totalAmountCcy;
    }

    public function setTotalAmountCcy(string $totalAmountCcy): static
    {
        $this->totalAmountCcy = $totalAmountCcy;

        return $this;
    }

    public function getTotalAmountMur(): ?float
    {
        return $this->totalAmountMur;
    }

    public function setTotalAmountMur(float $totalAmountMur): static
    {
        $this->totalAmountMur = $totalAmountMur;

        return $this;
    }

    public function getFundDate(): ?\DateTime
    {
        return $this->fundDate;
    }

    public function setFundDate(\DateTime $fundDate): static
    {
        $this->fundDate = $fundDate;

        return $this;
    }


    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }


}
