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
     * Chercher par référence
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
     * Chercher par nom du fund
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
     * Récupérer les funds d’un client sur une période
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

    /**
     * Récupérer les funds par nom et période (optionnelle)
     */
    public function findByNameAndPeriod(int $userId, string $fundName, ?\DateTimeInterface $startDate)
    {
        $qb = $this->createQueryBuilder('f')
            ->innerJoin('f.navId', 'n')
            ->addSelect('n')  
            ->where('f.userId = :userId')
            ->setParameter('userId', $userId);

        if ($fundName !== null && $fundName !== 'ALL') {
            $qb->andWhere('f.fundName = :name')
            ->setParameter('name', $fundName);
        }

        if ($startDate !== null) {
            $qb->andWhere('f.fundDate >= :startDate')
            ->setParameter('startDate', $startDate);
        }

        return $qb->orderBy('f.fundDate', 'ASC')
                ->getQuery()
                ->getResult();
    }

    /**
     * Récupérer les funds par userId
     */
    public function findByUserId(
        int $userId, 
        ?string $sortField = null, 
        string $sortOrder = 'ASC',
        ?string $reference = null,
        ?string $fundName = null
    )
    {
        $qb = $this->createQueryBuilder('f')
                ->innerJoin('f.navId', 'n')
                ->addSelect('n')  
                ->where('f.userId = :userId')
                ->setParameter('userId', $userId);
        
        // --- Filtres optionnels ---
        if ($reference !== null && $reference !== '') {
            $qb->andWhere('f.reference LIKE :reference')
            ->setParameter('reference', '%' . $reference . '%');
        }

        if ($fundName !== null && $fundName !== '') {
            $qb->andWhere('f.fundName LIKE :fundName')
            ->setParameter('fundName', '%' . $fundName . '%');
        }

        // mapping des champs autorisés pour le tri
        $allowedSortFields = [
            'reference'       => 'f.reference',
            'fundName'        => 'f.fundName',
            'noOfShares'      => 'f.noOfShares',
            'nav'             => 'f.nav',
            'totalAmountCcy'  => 'f.totalAmountCcy',
            'totalAmountMur'  => 'f.totalAmountMur',
            'fundDate'        => 'f.fundDate'
        ];
        

        if ($sortField && isset($allowedSortFields[$sortField])) {
            // sécuriser la direction
            $sortOrder = strtoupper($sortOrder);
            if (!in_array($sortOrder, ['ASC','DESC'])) {
                $sortOrder = 'ASC';
            }

            $qb->orderBy($allowedSortFields[$sortField], $sortOrder);
        } else {
            $qb->orderBy('f.reference', 'ASC'); // tri par défaut
        }

        return $qb->getQuery()->getResult();
    }
}