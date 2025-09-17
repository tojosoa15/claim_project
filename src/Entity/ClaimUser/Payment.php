<?php

namespace App\Entity\ClaimUser;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\PaymentController;
use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 *
 * @ORM\Table(name="payment", indexes={
 *     @ORM\Index(name="fk_paiement_status", columns={"status_id"}),
 *     @ORM\Index(name="fk_paiement_users", columns={"users_id"}),
 *     @ORM\Index(name="fk_paiement_claims", columns={"claim_number"})
 * })
 * @ORM\Entity
 */
#[ApiResource(
    operations: [
        // Liste paiement d'un utilisateur
        new GetCollection(
            uriTemplate: '/api/payments',
            controller: PaymentController::class . '::__invoke',
            parameters: [ 
                'email'         => new QueryParameter(),
                'status'        => new QueryParameter(),
                'invoiceNo'     => new QueryParameter(),
                'claimNo'       => new QueryParameter(),
                'sortBy'        => new QueryParameter(),
                'page'          => new QueryParameter(),
                'pageSize'      => new QueryParameter()
            ],
        ),
        // Card pour les paiements
        new Get(
            uriTemplate: '/api/payment/card-stats',
            controller: PaymentController::class . '::getCardStatsPaiment',
            parameters: [ 
                'email' => new QueryParameter()
            ],
        ),
        // Liste paiement d'un utilisateur
        new GetCollection(
            uriTemplate: '/api/payment/export',
            controller: PaymentController::class . '::paymentExport',
            parameters: [ 
                'email'     => new QueryParameter(),
                'startDate' => new QueryParameter(),
                'endDate'   => new QueryParameter(),
                'format'    => new QueryParameter()
            ],
        ),
        // Détail d'un paiement
        new Get(
            uriTemplate: '/api/payment',
            controller: PaymentController::class . '::getDetailPaiement',
            parameters: [ 
                'email'     => new QueryParameter(),
                'invoiceNo' => new QueryParameter(),
            ],
        ),

        // Liste paiement d'un utilisateur
        new Get(
            uriTemplate: '/api/payment/download-invoice',
            controller: PaymentController::class . '::downloadInvoice',
            parameters: [ 
                'email'    => new QueryParameter(),
                'invoiceNo' => new QueryParameter()
            ],
        ),
    ]
)]
class Payment
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
     * @ORM\Column(name="invoice_no", type="string", length=100, nullable=false)
     */
    private $invoiceNo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_submitted", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $dateSubmitted = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_payment", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $datePayment = null;

    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @var int
     *
     * @ORM\Column(name="users_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="claim_number", type="string", length=100, nullable=false)
     */
    private $claimNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="claim_amount", type="float", precision=10, scale=0, nullable=false)
     */
    private $claimAmount;

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNo(): ?string
    {
        return $this->invoiceNo;
    }

    public function setInvoiceNo(string $invoiceNo): self
    {
        $this->invoiceNo = $invoiceNo;
        return $this;
    }

    public function getDateSubmitted(): ?\DateTime
    {
        return $this->dateSubmitted;
    }

    public function setDateSubmitted(\DateTime $dateSubmitted): self
    {
        $this->dateSubmitted = $dateSubmitted;
        return $this;
    }

    public function getDatePayment(): ?\DateTime
    {
        return $this->datePayment;
    }

    public function setDatePayment(\DateTime $datePayment): self
    {
        $this->datePayment = $datePayment;
        return $this;
    }

    public function getStatusId(): ?int
    {
        return $this->statusId;
    }

    public function setStatusId(int $statusId): self
    {
        $this->statusId = $statusId;
        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->userId;
    }

    public function setUsersId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getClaimNumber(): ?string
    {
        return $this->claimNumber;
    }

    public function setClaimNumber(string $claimNumber): self
    {
        $this->claimNumber = $claimNumber;
        return $this;
    }

    public function getClaimAmount(): ?float
    {
        return $this->claimAmount;
    }

    public function setClaimAmount(float $claimAmount): self
    {
        $this->claimAmount = $claimAmount;
        return $this;
    }
}