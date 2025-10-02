<?php

namespace App\Repository;

use App\Entity\Scs\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Récupérer les transactions d'un utilisateur avec filtres, tri et pagination
     */
    public function findByUserIdWithFilters(array $params): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select(
                't.id AS transaction_id',
                        'f.id AS fund_id',
                        't.transactionDate AS date',
                        'f.fundName AS fund_name',
                        'f.reference AS sub_account_reference',
                        'tt.name AS transaction_type',
                        't.cnNumber AS cn_number',
                        't.noOfUnits AS no_of_units',
                        't.netAmountInvRedeemed AS net_amount_inv_redeemed',
                        't.currency AS currency',
                        'f.totalAmountMur AS net_amount_mur'
            )
            ->join('t.fundId', 'f')
            ->join('t.typeId', 'tt')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', intval($params['userId']));

        // Filtre par fund name
        // if (!empty($params['searchFundName'])) {
        //     $qb->andWhere('f.fundName LIKE :fundName')
        //     ->setParameter('fundName', '%' . $params['searchFundName'] . '%');
        // }
        // if (!empty($params['searchFundName'])) {
        //     $qb->andWhere('f.fundName IN  (:fundName)')
        //        ->setParameter('fundName', $params['searchFundName']);
        // }
        if (!empty($params['searchFundName'])) {
            if (is_array($params['searchFundName'])) {
                // Cas recherche multiple stricte
                $qb->andWhere('f.fundName IN (:fundName)')
                ->setParameter('fundName', $params['searchFundName']);
            } else {
                // Cas recherche rapide
                $qb->andWhere('f.fundName LIKE :fundName')
                ->setParameter('fundName', '%' . $params['searchFundName'] . '%');
            }
        }

        // Filtre par référence de compte
        // if (!empty($params['searchReference'])) {
        //     $qb->andWhere('f.reference LIKE :reference')
        //        ->setParameter('reference', '%'.$params['searchReference'].'%');
        // }
        // if (!empty($params['searchReference'])) {
        //     $qb->andWhere('f.reference IN  (:reference)')
        //        ->setParameter('reference', $params['searchReference']);
        // }
        if (!empty($params['searchReference'])) {
            if (is_array($params['searchReference'])) {
                // Cas recherche multiple stricte
                $qb->andWhere('f.reference IN (:reference)')
                ->setParameter('reference', $params['searchReference']);
            } else {
                // Cas recherche rapide
                $qb->andWhere('f.reference LIKE :reference')
                ->setParameter('reference', '%' . $params['searchReference'] . '%');
            }
        }

        // Filtre par type de transaction
        // if (!empty($params['searchTransactionType'])) {
        //     $qb->andWhere('tt.name LIKE :transactionType')
        //        ->setParameter('transactionType', '%'.$params['searchTransactionType'].'%');
        // }
        if (!empty($params['searchTransactionType'])) {
            $qb->andWhere('tt.name IN  (:ttName)')
               ->setParameter('ttName', $params['searchTransactionType']);
        }

        if (!empty($params['searchCnNumber'])) {
            $qb->andWhere('t.cnNumber LIKE :cnNumber')
               ->setParameter('cnNumber', '%'.$params['searchCnNumber'].'%');
        }

        // Filtre par currency
        // if (!empty($params['searchCurrency'])) {
        //     $qb->andWhere('t.currency LIKE :currency')
        //        ->setParameter('currency', '%'.$params['searchCurrency'].'%');
        // }

        if (!empty($params['searchCurrency'])) {
            $qb->andWhere('t.currency IN  (:currency)')
               ->setParameter('currency', $params['searchCurrency']);
        }

        // Filtre par date si startDate et endDate sont définies
        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $startDate = new \DateTime($params['startDate']);
            $endDate   = new \DateTime($params['endDate']);
            
            // On inclut la fin de journée pour être sûr de récupérer toutes les transactions du jour de fin
            $endDate->setTime(23, 59, 59);

            $qb->andWhere('t.transactionDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
        }


        // Tri
        if (!empty($params['sortBy'])) {
            [$field, $order] = explode('-', $params['sortBy']);
            $qb->orderBy($field, $order);
        } else {
            $qb->orderBy('t.transactionDate', 'DESC');
        }

        // Pagination
        $page  = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $qb->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

        $items = $qb->getQuery()->getArrayResult();

        // Compter total
        $qbCount = clone $qb;
        $qbCount->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->setFirstResult(null)
                ->setMaxResults(null)
                ->select('COUNT(t.id)');
        $total = (int) $qbCount->getQuery()->getSingleScalarResult();

        // Formatage date
        foreach ($items as &$item) {
            if ($item['date'] instanceof \DateTimeInterface) {
                $item['date'] = $item['date']->format('d-M-Y');
            }
        }

        $totalPages = (int) ceil($total / $limit);

        return [
            'items'             => $items,
            'total_transaction' => $total,
            'total_pages'       => $totalPages,
            'current_page'      => $page,
            'previous_page'     => $page > 1 ? $page - 1 : 1,
            'next_page'         => $page < $totalPages ? $page + 1 : $totalPages,
            'page_size'         => $limit,
        ];
    }

}