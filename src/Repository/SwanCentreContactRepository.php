<?php

namespace App\Repository;

use App\Entity\Scs\SwanCentreContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SwanCentreContact>
 */
class SwanCentreContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SwanCentreContact::class);
    }
    public function findFirstContact(): ?SwanCentreContact
{
    return $this->createQueryBuilder('c')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
}


}
