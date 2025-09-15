<?php

namespace App\Repository;

use App\Entity\ClaimUser\FinancialInformations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FinancialInformations>
 */
class FinancialInformationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialInformations::class);
    }

}
