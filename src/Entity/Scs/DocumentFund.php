<?php

namespace App\Entity\Scs;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\DocumentFundController;
use Doctrine\ORM\Mapping as ORM;
use Dom\Document;

/**
 * DocumentFund
 *
 * @ORM\Table(name="document_fund")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=App\Repository\DocumentFundRepository::class)
 */
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/documents',
            controller: DocumentFundController::class . '::getDocumentByCategory',
            parameters: [ 
                'categoryId'        => new QueryParameter(),
                'searchDocName'     => new QueryParameter(),
                'searchFundName'    => new QueryParameter(),
                'sortBy'            => new QueryParameter(),
            ]
        ),    
    ],
)]
class DocumentFund
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $docName;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path;

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
     * @var \DocumentCategory
     * 
     * @ORM\ManyToOne(targetEntity="DocumentCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $categoryId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocName(): ?string
    {
        return $this->docName;
    }

    public function setDocName(string $docName): static
    {
        $this->docName = $docName;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getCategoryId(): ?DocumentCategory
    {
        return $this->categoryId;
    }

    public function setCategoryId(?DocumentCategory $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }

}
