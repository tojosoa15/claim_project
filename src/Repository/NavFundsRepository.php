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

    /**
     * Récupère les 7 dernières valeurs pour chaque code_name
     *
     * @return array<string, NavFunds[]> Tableau associatif avec code_name comme clé et tableau de NavFunds comme valeur
     */
    public function findLastUniqueByCodeName(): array
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.codeName', 'ASC')
            ->addOrderBy('n.typeNav', 'ASC')
            ->addOrderBy('n.navDate', 'DESC');

        $results = $qb->getQuery()->getResult();

        $grouped = [];
        foreach ($results as $nav) {
            $key = $nav->getCodeName() . '_' . $nav->getTypeNav();

            // si on n’a pas encore pris ce couple code_name + type_nav → on prend le plus récent
            if (!isset($grouped[$key])) {
                $grouped[$key] = $nav;
            }
        }

        return $grouped;
    }


}