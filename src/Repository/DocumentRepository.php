<?php

namespace App\Repository;

use App\Entity\ClaimUser\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    // EmploymentInformationsRepository.php
    public function findByUserAndDocumentId(int $userId, ?int $documentId = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.users = :userId')
            ->setParameter('userId', $userId);

        if ($documentId !== null) {
            $qb->andWhere('d.id = :documentId')
               ->setParameter('documentId', $documentId);
        }

        return $qb->getQuery()->getResult();
    }


}
