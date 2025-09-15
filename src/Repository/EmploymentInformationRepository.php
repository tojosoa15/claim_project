<?php

namespace App\Repository;

use App\Entity\ClaimUser\EmploymentInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmploymentInformation>
 */
class EmploymentInformationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmploymentInformation::class);
    }

}
