<?php

namespace App\Repository;

use App\Entity\Scs\NavFunds;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NavFunds>
 */
class NavFundsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NavFunds::class);
    }

    /**
     * Exemple : chercher par type de nav
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.typeNav = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}