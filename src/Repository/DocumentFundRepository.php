<?php

namespace App\Repository;

use App\Entity\Scs\DocumentFund;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentFundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentFund::class);
    }

    /**
     * Récupère les documents par id de catégorie
     */
    public function findByCategory(array $params): array
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.categoryId', 'c')
            ->leftJoin('d.fundId', 'f')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $params['categoryId'])
            ->orderBy('d.createdAt', 'DESC');

        if (!empty($params['searchDocName'])) {
            $qb->andWhere('d.docName LIKE :docName')
            ->setParameter('docName', '%' . $params['searchDocName'] . '%');
        }

        if (!empty($params['searchFundName'])) {
            $qb->andWhere('f.fundName IN  (:fundName)')
               ->setParameter('fundName', $params['searchFundName']);
        }

        // Tri
        if (!empty($params['sortBy'])) {
            [$field, $order] = explode('-', $params['sortBy']);
            // dd($field, $order);
            $qb->orderBy('d.createdAt', $order);
        } else {
            $qb->orderBy('d.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
