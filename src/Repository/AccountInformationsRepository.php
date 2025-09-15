<?php

namespace App\Repository;

use App\Entity\ClaimUser\AccountInformations;
use App\Entity\ClaimUser\FinancialInformations;
use App\Entity\ClaimUser\EmploymentInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountInformations>
 */
class AccountInformationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountInformations::class);
    }

    // AccountInformationsRepository.php
    public function findByUserId(int $userId): ?AccountInformations
    {
        return $this->findOneBy(['users' => $userId]);
    }

}
