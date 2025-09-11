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
        if (!empty($params['searchFundName'])) {
            $qb->andWhere('f.fundName LIKE :fundName')
            ->setParameter('fundName', '%' . $params['searchFundName'] . '%');
        }

        // Filtre par référence de compte
        if (!empty($params['searchReference'])) {
            $qb->andWhere('f.reference LIKE :reference')
               ->setParameter('reference', '%'.$params['searchReference'].'%');
        }

        // Filtre par type de transaction
        if (!empty($params['searchTransactionType'])) {
            $qb->andWhere('tt.name LIKE :transactionType')
               ->setParameter('transactionType', '%'.$params['searchTransactionType'].'%');
        }

         // Filtre par type de transaction
        if (!empty($params['searchCurrency'])) {
            $qb->andWhere('t.currency LIKE :currency')
               ->setParameter('currency', '%'.$params['searchCurrency'].'%');
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

        // Count avec les mêmes filtres
        $qbCount = clone $qb;
        $qbCount->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->select('COUNT(t.id)');
        $total = $qbCount->getQuery()->getSingleScalarResult();

        // Formatage date
        foreach ($items as &$item) {
            if ($item['date'] instanceof \DateTimeInterface) {
                $item['date'] = $item['date']->format('d-M-Y');
            }
        }

        return [
            'items' => $items,
            'total' => (int) $total,
            'page'  => (int) $page,
            'limit' => (int) $limit,
        ];
    }

}