<?php

namespace App\Controller;

use App\Entity\ClaimUser\Claims;
use App\Entity\Scs\ForexRate;
use App\Entity\Scs\Fund;
use App\Entity\Scs\NavFunds;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class DashboardViewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        ManagerRegistry $doctrine,
    ) {
        $this->em = $doctrine->getManager('scs_db');
    }

    /**
     * Liste nav des funds
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getNavOfTheFunds(Request $request): JsonResponse
    {
        $navFundsFormat = [];

        try {
            $navFunds = $this->em->getRepository(NavFunds::class)->findAll();

            foreach ($navFunds as $nav) {
                $navFundsFormat[] = [
                    'id'        => $nav->getId(),
                    'code_name' => $nav->getCodeName(),
                    'type_nav'  => $nav->getTypeNav(),
                    'value'     => $nav->getValue()
                ];
            }

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful list nav of the funds.',
                'data'      => $navFundsFormat
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    =>  'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => $e->getMessage()
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Liste funds d'un client
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getListFundsPerformance(Request $request): JsonResponse
    {
        $fundFormat     = [];
        $allNavs        = [];
        $lastFund       = null;
        $params         = $request->query->all();
        $userId         = $params['userId'] ?? null;    
        $fundName       = $params['fundName'] ?? null;    
        $period         = $params['period'] ?? null;
        $searchRef      = $params['searchRef'] ?? null;
        $searchFundName = $params['searchFundName'] ?? null;

        if (empty($userId ) || $userId  === null) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_BAD_REQUEST,
                    'message'   => 'userId parameter is required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $sortBy     = $params['sortBy'] ?? null;
            $sortField  = null;
            $sortOrder  = 'ASC';

            if ($sortBy) {
                // exemple: fundName_DESC
                $parts      = explode('-', $sortBy);
                $sortField  = $parts[0] ?? null;
                $sortOrder  = strtoupper($parts[1] ?? 'ASC');

                // sécuriser la direction
                if (!in_array($sortOrder, ['ASC', 'DESC'])) {
                    $sortOrder = 'ASC';
                }
            }

            // Aucun paramètre renseigné -> retourner tout
            if ((empty($fundName) || $fundName === null) && (empty($period) || $period === null)) {
                $funds = $this->em->getRepository(Fund::class)->findByUserId($userId, $sortField, $sortOrder, $searchRef, $searchFundName);
                
                // Seulement pour la liste 
                foreach ($funds as $entity) {
                    if ($entity instanceof \App\Entity\Scs\NavFunds) {
                        $fund = $entity->getFundId();
                        $fundId = $fund->getId();

                        // Si pas encore défini ou si ce nav est plus récent que le précédent
                        if (
                            !isset($fundFormat[$fundId]) ||
                            $entity->getNavDate() > new \DateTime($fundFormat[$fundId]['nav']['nav_date'])
                        ) {
                            $fundFormat[] = [
                                'fund_id'           => $fundId,
                                'reference'         => $fund->getReference(),
                                'fund_name'         => $fund->getFundName(),
                                'no_of_shares'      => $fund->getNoOfShares(),
                                'total_amount_ccy'  => $fund->getTotalAmountCcy(),
                                'total_amount_mur'  => $fund->getTotalAmountMur(),
                                // 'nav_id'            => $entity->getId(),
                                'avg_nav'           => $entity->getValue(),
                                'c_name'            => $entity->getTypeNav(),
                                'nav'               => $entity->getTypeNav().' '.$entity->getValue(),
                                'nav_date'          => $entity->getNavDate()?->format('Y-m-d'),
                                'month_name'        => $entity->getNavDate()?->format('F'),
                                'month_number'      => $entity->getNavDate()?->format('m'),
                                'year'              => $entity->getNavDate()?->format('Y'),
                                'year_month'        => $entity->getNavDate()?->format('d-M-Y')
                            ];
                        }
                    }
                }

                return new JsonResponse([
                    'status'    => 'success',
                    'code'      => JsonResponse::HTTP_OK,
                    'message'   => 'Successful list of funds by customer.',
                    'data'      => $fundFormat
                ], JsonResponse::HTTP_OK);

            } elseif ((empty($fundName) || $fundName === null) xor (empty($period) || $period === null)) {
                throw new \InvalidArgumentException("You must fill in both ‘fundName’ and ‘period’.");

            // Les deux sont renseignés -> appliquer le filtre
            } else {
                // calcul de la date de début en fonction de la période
                switch ($period) {
                    case '1M':
                        $startDate = (new DateTime())->sub(new DateInterval('P1M'));
                        break;
                    case '3M':
                        $startDate = (new DateTime())->sub(new DateInterval('P3M'));
                        break;
                    case '6M':
                        $startDate = (new DateTime())->sub(new DateInterval('P6M'));
                        break;
                    case 'YTD':
                        $startDate = new DateTime('first day of January ' . date('Y'));
                        break;
                    case '1Y':
                        $startDate = (new DateTime())->sub(new DateInterval('P1Y'));
                        break;
                    case 'ALL':
                    default:
                        $startDate = null; // pas de limite
                }   

                $funds = $this->em->getRepository(Fund::class)
                        ->findByNameAndPeriod($userId ,$fundName, $startDate);

                // Liste des navs
                foreach ($funds as $entity) {
                    if ($entity instanceof \App\Entity\Scs\NavFunds) {
                        $fund = $entity->getFundId();
                        $fundId = $fund->getId();
                        // --- Format pour TOUTES les NAVs ---
                        $allNavs[] = [
                            'fund_id'           => $fundId,
                            'reference'         => $fund->getReference(),
                            'fund_name'         => $fund->getFundName(),
                            'no_of_shares'      => $fund->getNoOfShares(),
                            'total_amount_ccy'  => $fund->getTotalAmountCcy(),
                            'total_amount_mur'  => $fund->getTotalAmountMur(),
                            'avg_nav'           => $entity->getValue(),
                            'c_name'            => $entity->getTypeNav(),
                            'nav'               => $entity->getTypeNav().' '.$entity->getValue(),
                            'nav_date'          => $entity->getNavDate()?->format('Y-m-d'),
                            'month_name'        => $entity->getNavDate()?->format('F'),
                            'month_number'      => $entity->getNavDate()?->format('m'),
                            'year'              => $entity->getNavDate()?->format('Y'),
                            'year_month'        => $entity->getNavDate()?->format('d-M-Y'),
                        ];
                    }
                }

                return new JsonResponse([
                    'status'    => 'success',
                    'code'      => JsonResponse::HTTP_OK,
                    'message'   => 'Successful list of funds by customer.',
                    'data'      => $allNavs
                ], JsonResponse::HTTP_OK);
            }

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => $e->getMessage()
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

    }

     /**
     * Liste taux de change
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllForexRates(Request $request): JsonResponse
    {
        $forexRateFormat = [];

        try {
            $forexRate = $this->em->getRepository(ForexRate::class)->findAll();

            foreach ($forexRate as $nav) {
                $forexRateFormat[] = [
                    'id'        => $nav->getId(),
                    'code_name' => $nav->getType(),
                    'value'     => $nav->getValue()
                ];
            }

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful list nav of the funds.',
                'data'      => $forexRateFormat
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    =>  'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => $e->getMessage()
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

   /**
     * Dernier nav et date de valuation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNavLastValuationDate(Request $request): JsonResponse
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
            $funds = $this->em->getRepository(Fund::class)->findByUserId($userId);

            if (empty($funds)) {
                return $this->json([
                    'status'  => 'success',
                    'code'    => JsonResponse::HTTP_OK,
                    'message' => 'No funds found for this user.',
                    'data'    => null,
                ], JsonResponse::HTTP_OK);
            }

            // Récupérer le fund avec la date la plus récente
            $lastFund = array_reduce($funds, function ($carry, $fund) {
                return ($carry === null || $fund->getFundDate() > $carry->getFundDate())
                    ? $fund
                    : $carry;
            });

            return $this->json([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful Nav and last valuation date.',
                'data'    => [
                    'nav_per_share'  => $lastFund->getNav(),
                    'valuation_date' => $lastFund?->getFundDate()?->format('d M Y'),
                ],
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