<?php

namespace App\Repository;

use App\Entity\Scs\Fund;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fund>
 */
class FundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fund::class);
    }

    /**
     * Exemple : chercher par référence
     */
    public function findByReference(string $reference): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.reference = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple : chercher par nom du fund
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.fundName = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple : récupérer les funds d’un client sur une période
     */
    public function findByCustomerAndPeriod(string $reference, \DateTimeInterface $period): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.reference = :ref')
            ->andWhere('f.fundDate >= :period')
            ->setParameter('ref', $reference)
            ->setParameter('period', $period)
            ->getQuery()
            ->getResult();
    }


    public function findByNameAndPeriod(string $fundName, ?\DateTimeInterface $startDate)
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.fundName = :name')
            ->setParameter('name', $fundName);

        if ($startDate !== null) {
            $qb->andWhere('f.fundDate >= :startDate')
            ->setParameter('startDate', $startDate);
        }

        return $qb->orderBy('f.fundDate', 'ASC')
                ->getQuery()
                ->getResult();
    }
}