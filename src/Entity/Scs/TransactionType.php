<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\TransactionHistoryController;
use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionType
 *
 * @ORM\Table(name="transaction_type")
 * @ORM\Entity
 */
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/transaction-types',
            controller: TransactionHistoryController::class . '::getAllDocumentType'
        ),    
    ],
)]
class TransactionType
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
     * @ORM\Column(name="code", type="string", length=150, nullable=true)
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=150, nullable=true)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getStatusName(): ?string
    {
        return $this->name;
    }

    public function setStatusName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

}