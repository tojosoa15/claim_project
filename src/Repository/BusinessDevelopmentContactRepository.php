<?php

namespace App\Repository;

use App\Entity\Scs\BusinessDevelopmentContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusinessDevelopmentContact>
 */
class BusinessDevelopmentContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessDevelopmentContact::class);
    }

      /**
     * Récupère le premier contact Business Development
     */
    public function findFirstContact(): ?BusinessDevelopmentContact
    {
        return $this->createQueryBuilder('c')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
