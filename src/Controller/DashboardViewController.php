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
        $fundFormat = [];
        $lastFund   = null;
        $params     = $request->query->all();
        $userId     = $params['userId'] ?? null;    
        $fundName   = $params['fundName'] ?? null;    
        $period     = $params['period'] ?? null;

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
                $funds = $this->em->getRepository(Fund::class)->findByUserId($userId, $sortField, $sortOrder);
                // Seulement un des deux est renseigné -> erreur
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
            }

            foreach ($funds as $f) {
                // Traitement de "nav"
                $navParts   = explode(' ', $f->getNav()); // ex: ["MUR", "58.88"]
                $cName      = $navParts[0] ?? null;
                $avgNav     = isset($navParts[1]) ? (float) $navParts[1] : null;

                // Traitement de "fundDate"
                $fundDate   = $f->getFundDate();

                if ($fundDate) {
                    $dateObj        = $fundDate; // si c'est déjà un DateTime
                    $monthName      = $dateObj->format('F'); // "September"
                    setlocale(LC_TIME, 'fr_FR.UTF-8'); // pour avoir le mois en français
                    $monthNameFr    = strftime('%B', $dateObj->getTimestamp()); // "septembre"
                    $monthNumber    = (int)$dateObj->format('m'); // 9
                    $year           = (int)$dateObj->format('Y'); // 2025
                    $yearMonth      = $dateObj->format('d M Y'); // "04 Sep 2025"

                    // garder le dernier fund
                    if ($lastFund === null || $dateObj > $lastFund->getFundDate()) {
                        $lastFund = $f;
                    }
                } else {
                    $monthNameFr = $monthNumber = $year = $yearMonth = null;
                }

                $fundFormat[] = [
                    'id'                => $f->getId(),
                    'customer_id'       => $f->getUserId(),
                    'reference'         => $f->getReference(),
                    'fund_name'         => $f->getFundName(),
                    'no_of_shares'      => $f->getNoOfShares(),
                    'nav'               => $f->getNav(),
                    'total_amount_ccy'  => $f->getTotalAmountCcy(),
                    'total_amount_mur'  => $f->getTotalAmountMur(),
                    'fund_date'         => $fundDate ? $fundDate->format('Y-m-d') : null,
                    'avg_nav'           => $avgNav,
                    'c_name'            => $cName,
                    'month_name'        => $monthNameFr,
                    'month_number'      => $monthNumber,
                    'year'              => $year,
                    'year_month'        => $yearMonth
                ];
            }

            // maintenant on récupère avg_nav et year_month du dernier enregistrement
            $lastAvgNav     = null;
            $lastYearMonth  = null;

            if ($lastFund) {
                $navParts = explode(' ', $lastFund->getNav());
                $lastAvgNav = isset($navParts[1]) ? (float) $navParts[1] : null;
                $lastYearMonth = $lastFund->getFundDate()->format('d M Y');
            }

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful list of funds by customer.',
                // 'last'      => [
                //     'nav_per_share'     => $lastAvgNav,
                //     'valuation_date'    => $lastYearMonth
                // ],
                'data'      => $fundFormat
            ], JsonResponse::HTTP_OK);

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