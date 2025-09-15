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
    public function findByUserId(int $userId): array
{
    return $this->createQueryBuilder('d')
        ->where('d.users = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult();
}


}
