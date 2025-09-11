<?php

namespace App\Controller;

use App\Entity\Scs\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class TransactionHistoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        ManagerRegistry $doctrine,
    ) {
        $this->em = $doctrine->getManager('scs_db');
    }

    /**
     * Récupérer l’historique des transactions d’un utilisateur
     */
    public function getAllTransactionHistory(Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');

        if (!$userId) {
            return $this->json([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_BAD_REQUEST,
                'message' => 'userId parameter is required',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            // $transactions = $this->em->getRepository(Transaction::class)->findByUserId(intval($userId));
            $params = $request->query->all();

            $transactions = $this->em
                ->getRepository(Transaction::class)
                ->findByUserIdWithFilters($params);

            if (empty($transactions)) {
                return $this->json([
                    'status'  => 'success',
                    'code'    => JsonResponse::HTTP_OK,
                    'message' => 'No transaction found for this user.',
                    'data'    => null,
                ], JsonResponse::HTTP_OK);
            }

            return $this->json([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful transaction list.',
                'data'    => $transactions,
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
