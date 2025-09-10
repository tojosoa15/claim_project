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
    public function findByNameAndPeriod(
        int $userId,
        ?string $fundName,
        ?\DateTimeInterface $startDate,
        ?\DateTimeInterface $endDate = null
    ) {
        $qb = $this->createQueryBuilder('f')
            ->innerJoin('App\Entity\Scs\NavFunds', 'n', 'WITH', 'n.fundId = f.id')
            ->addSelect('n')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId);

        // Filtrer par fund name (sauf si ALL)
        if ($fundName !== null && strtoupper($fundName) !== 'ALL') {
            $qb->andWhere('f.fundName = :name')
            ->setParameter('name', $fundName);
        }

        // Filtrer par date de début
        if ($startDate !== null) {
            $qb->andWhere('n.navDate >= :startDate')
            ->setParameter('startDate', $startDate);
        }

        // Optionnel : filtrer aussi par date de fin si fourni
        if ($endDate !== null) {
            $qb->andWhere('n.navDate <= :endDate')
            ->setParameter('endDate', $endDate);
        }

        return $qb->orderBy('n.navDate', 'ASC')
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
        // $qb = $this->createQueryBuilder('f')
        //         // ->innerJoin('f.navId', 'n')
        //         // ->addSelect('n')  
        //         ->innerJoin('App\Entity\Scs\NavFunds', 'n', 'WITH', 'n.fundId = f.id')
        //         ->addSelect('n')
        //         ->where('f.userId = :userId')
        //         ->setParameter('userId', $userId);
        $qb = $this->createQueryBuilder('f')
            ->innerJoin('App\Entity\Scs\NavFunds', 'n', 'WITH', 'n.fundId = f.id')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('n.navDate = (
                SELECT MAX(n2.navDate)
                FROM App\Entity\Scs\NavFunds n2
                WHERE n2.fundId = f.id
            )')
            ->addSelect('n');
        
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
            'nav'             => 'n.value',
            'totalAmountCcy'  => 'f.totalAmountCcy',
            'totalAmountMur'  => 'f.totalAmountMur'
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