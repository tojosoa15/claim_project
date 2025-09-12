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
            ->orderBy('n.navDate', 'DESC')
            ->addOrderBy('n.codeName', 'ASC')
            ->addOrderBy('n.typeNav', 'ASC');

        $results = $qb->getQuery()->getResult();

        $grouped = [];
        foreach ($results as $nav) {
            $key = $nav->getCodeName() . '_' . $nav->getTypeNav();

            // on ne garde que le premier (le plus récent grâce au tri DESC)
            if (!isset($grouped[$key])) {
                $grouped[$key] = $nav;
            }
        }

        // On récupère seulement 7 résultats uniques
        return array_slice(array_values($grouped), 0, 7);
    }

    /**
     * Récupère les 7 dernières entrées de NavFunds
     *
     * @return NavFunds[] Liste des 7 dernières entrées de NavFunds
     */
    public function findLastSeven(): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.navDate', 'DESC')
            ->setMaxResults(7)
            ->getQuery()
            ->getResult();
    }


}