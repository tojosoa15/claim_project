<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\TransactionHistoryController;
use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=App\Repository\TransactionRepository::class)
 */
#[ApiResource(
    operations: [
        // Récupérer l’historique des transactions d’un utilisateur
        new GetCollection(
            uriTemplate: '/api/transactions',
            controller: TransactionHistoryController::class . '::getAllTransactionHistory',
            parameters: [
                'userId' => new QueryParameter(
                    description: 'ID de l’utilisateur (obligatoire)',
                    required: true,
                    schema: ['type' => 'integer']
                ),
                'page' => new QueryParameter(
                    description: 'Numéro de page (par défaut: 1)',
                    required: false,
                    schema: ['type' => 'integer']
                ),
                'limit' => new QueryParameter(
                    description: 'Nombre d’éléments par page (par défaut: 10)',
                    required: false,
                    schema: ['type' => 'integer']
                ),
                'sortBy' => new QueryParameter(
                    description: 'Champ et ordre de tri. Exemple: date-DESC, fund_name-ASC',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchFundName' => new QueryParameter(
                    description: 'Recherche par nom du fond',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchReference' => new QueryParameter(
                    description: 'Recherche par référence de compte',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchTransactionType' => new QueryParameter(
                    description: 'Recherche par type de transaction',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchCnNumber' => new QueryParameter(
                    description: 'Recherche par cn number',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchCurrency' => new QueryParameter(
                    description: 'Recherche par currency',
                    required: false,
                    schema: ['type' => 'string']
                ),
            ]
        ),    
        // Export des transactions    
        new GetCollection(
            uriTemplate: '/api/transactions/export',
            controller: TransactionHistoryController::class . '::transactionExport',
            parameters: [
                'userId' => new QueryParameter(
                    description: 'ID de l’utilisateur (obligatoire)',
                    required: true,
                    schema: ['type' => 'integer']
                ),
                'page' => new QueryParameter(
                    description: 'Numéro de page (par défaut: 1)',
                    required: false,
                    schema: ['type' => 'integer']
                ),
                'limit' => new QueryParameter(
                    description: 'Nombre d’éléments par page (par défaut: 10)',
                    required: false,
                    schema: ['type' => 'integer']
                ),
                'sortBy' => new QueryParameter(
                    description: 'Champ et ordre de tri. Exemple: date-DESC, fund_name-ASC',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchFundName' => new QueryParameter(
                    description: 'Recherche par nom du fond',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchReference' => new QueryParameter(
                    description: 'Recherche par référence de compte',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchTransactionType' => new QueryParameter(
                    description: 'Recherche par type de transaction',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchCnNumber' => new QueryParameter(
                    description: 'Recherche par cn number',
                    required: false,
                    schema: ['type' => 'string']
                ),
                'searchCurrency' => new QueryParameter(
                    description: 'Recherche par currency',
                    required: false,
                    schema: ['type' => 'string']
                ),
            ]
        ),
    ],
)]
class Transaction
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
     * @ORM\Column(name="cn_number", type="string", length=45, nullable=false)
     */
    private $cnNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="no_of_units", type="float", precision=10, scale=0, nullable=false)
     */
    private $noOfUnits;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=45, nullable=false)
     */
    private $currency;

    /**
     * @var float
     *
     * @ORM\Column(name="net_amount_inv_redeemed", type="float", precision=10, scale=0, nullable=false)
     */
    private $netAmountInvRedeemed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="transaction_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $transactionDate = 'CURRENT_TIMESTAMP';

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
     * @var \Fund
     *
     * @ORM\ManyToOne(targetEntity="TransactionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id ", referencedColumnName="id")
     * })
     */
    private $typeId;
   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCnNumber(): ?string
    {
        return $this->cnNumber;
    }

    public function setCnNumber(string $cnNumber): static
    {
        $this->cnNumber = $cnNumber;

        return $this;
    }

    public function getNoOfUnits(): ?float
    {
        return $this->noOfUnits;
    }

    public function setNoOfUnits(float $noOfUnits): static
    {
        $this->noOfUnits = $noOfUnits;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getNetAmountInvRedeemed(): ?float
    {
        return $this->netAmountInvRedeemed;
    }       
    public function setNetAmountInvRedeemed(float $netAmountInvRedeemed): static
    {
        $this->netAmountInvRedeemed = $netAmountInvRedeemed;

        return $this;
    }

     public function getTransactionDate(): ?\DateTime
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTime $transactionDate): static
    {
        $this->transactionDate = $transactionDate;

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

    public function getTypeId(): ?TransactionType 
    {
        return $this->typeId;
    }
    public function setTypeId(?TransactionType $typeId): static
    {
        $this->typeId = $typeId;

        return $this;
    }


}
