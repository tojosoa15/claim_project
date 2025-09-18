<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\TransactionHistoryController;
use Doctrine\ORM\Mapping as ORM;

/**
 * CurrencyType
 *
 * @ORM\Table(name="currency")
 * @ORM\Entity
 */
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/transactions/currency',
            controller: TransactionHistoryController::class . '::getAllCurrency'
        ),    
    ],
)]
class CurrencyType
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
     * @var string|null
     *
     * @ORM\Column(name="type_ccy", type="string", length=45, nullable=true)
     */
    private $typeCcy;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeCcy(): ?string
    {
        return $this->typeCcy;
    }

    public function setTypeCcy(?string $typeCcy): static
    {
        $this->typeCcy = $typeCcy;

        return $this;
    }

}