<?php

namespace App\Entity\Scs;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentCategory
 *
 * @ORM\Table(name="document_category")
 * @ORM\Entity
 */
class DocumentCategory
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
     * @ORM\Column(name="category_code", type="string", length=250, nullable=true)
     */
    private $categoryCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="category_name", type="string", length=250, nullable=true)
     */
    private $categoryName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryCode(): ?string
    {
        return $this->categoryCode;
    }

    public function setCategoryCode(?string $categoryCode): static
    {
        $this->categoryCode = $categoryCode;

        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): static
    {
        $this->categoryName = $categoryName;

        return $this;
    }

}