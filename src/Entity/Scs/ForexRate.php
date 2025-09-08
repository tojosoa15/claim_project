<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\DashboardViewController;
use App\Repository\ForexRateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForexRate
 *
 * @ORM\Table(name="forex_rate")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=App\Repository\ForexRateRepository::class)
 */
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/forex-rates',
            controller: DashboardViewController::class . '::getForexRates',
            // parameters: [ 
            //     'id' => new QueryParameter()
            // ]
        ),    
    ],
)]
class ForexRate
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
     * @ORM\Column(name="type", type="string", length=45, nullable=false)
     */
    private ?string $type = null;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=10, scale=0, nullable=false)
     */
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
