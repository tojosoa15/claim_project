<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BusinessDevelopmentContactRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\ContactUsController;

/**
 * SwanCentreContact
 *
 * @ORM\Table(name="business_development_contacts")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=BusinessDevelopmentContactRepository::class)
 */

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/contact-us',
            controller: ContactUsController::class . '::getAllContact',
        ),
    ]
)]
class BusinessDevelopmentContact
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
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private ?string $name = null;

/**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50, nullable=false)
     */
    private ?string $email = null;

   /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=50, nullable=false)
     */
    private ?string $phone = null;

      /**
     * @var string
     *
     * @ORM\Column(name="portable", type="string", length=50, nullable=false)
     */
    private ?string $portable = null;
    

    // ===== Getters & Setters =====
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPortable(): ?string
    {
        return $this->portable;
    }

    public function setPortable(string $portable): self
    {
        $this->portable = $portable;
        return $this;
    }
}
