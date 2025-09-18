<?php

namespace App\Controller;

use App\Entity\Scs\Transaction;
use App\Repository\TransactionTypeRepository;
use App\Repository\CurrencyRepository;
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
        private TransactionTypeRepository $transactionTypeRepository,
        private CurrencyRepository $currencyRepository
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

            // Manage array for fund name filter
            if (!empty($params['searchFundName']) && is_string($params['searchFundName'])) {
                $params['searchFundName'] = array_map('trim', explode(',', $params['searchFundName']));
            }

            // Manage array for reference filter
            if (!empty($params['searchReference']) && is_string($params['searchReference'])) {
                $params['searchReference'] = array_map('trim', explode(',', $params['searchReference']));
            }

            // Manage array for transaction type filter
            if (!empty($params['searchTransactionType']) && is_string($params['searchTransactionType'])) {
                $params['searchTransactionType'] = array_map('trim', explode(',', $params['searchTransactionType']));
            }

            // Manage array for currency filter
            if (!empty($params['searchCurrency']) && is_string($params['searchCurrency'])) {
                $params['searchCurrency'] = array_map('trim', explode(',', $params['searchCurrency']));
            }

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

    /**
     * Récupérer tous les types de documents
     */
    public function getAllDocumentType(): JsonResponse
    {
        try {
            $types = $this->transactionTypeRepository->findAllTypes();

            return $this->json([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful TransactionType list.',
                'data'    => $types,
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        public function getAllCurrency(): JsonResponse
    {
        try {
            $types = $this->currencyRepository->findAllCurrency();

            return $this->json([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful Currency list.',
                'data'    => $types,
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
