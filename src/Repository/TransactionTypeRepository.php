<?php

namespace App\Repository;

use App\Entity\Scs\TransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionType::class);
    }

    /**
     * Retourne tous les transaction types triés par nom
     */
    public function findAllTypes(): array
    {
        return $this->createQueryBuilder('tt')
            ->select('tt.id', 'tt.name')
            ->orderBy('tt.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}
