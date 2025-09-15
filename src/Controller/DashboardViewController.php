<?php

namespace App\Controller;

use App\Entity\ClaimUser\Claims;
use App\Entity\Scs\ForexRate;
use App\Entity\Scs\Fund;
use App\Entity\Scs\NavFunds;
use App\Repository\BusinessDevelopmentContactRepository;
use App\Repository\SwanCentreContactRepository;
use App\Repository\Scs\ContactUs;
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
            $lastNavs = $this->em->getRepository(NavFunds::class)->findLastUniqueByCodeName();

            $navFundsFormat = [];
            foreach ($lastNavs as $nav) {
                $navFundsFormat[] = [
                    'id'        => $nav?->getId() ?? null,
                    'code_name' => $nav?->getCodeName() ?? null,
                    'type_nav'  => $nav?->getTypeNav() ?? null,
                    'value'     => $nav?->getValue() ?? null,
                    'nav_date'  => $nav?->getNavDate()?->format('Y-m-d'),
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
                $funds = $this->em->getRepository(Fund::class)->findByUserId(intval($userId), $sortField, $sortOrder, $searchRef, $searchFundName);
                
                // Seulement pour la liste 
                foreach ($funds as $entity) {
                    if ($entity instanceof \App\Entity\Scs\NavFunds) {
                        $fund = $entity?->getFundId() ?? null;
                        $fundId = $fund?->getId() ?? null;

                        // Si pas encore défini ou si ce nav est plus récent que le précédent
                        if (
                            !isset($fundFormat[$fundId]) ||
                            $entity->getNavDate() > new \DateTime($fundFormat[$fundId]['nav']['nav_date'])
                        ) {
                            $fundFormat[] = [
                                'fund_id'           => $fundId,
                                'reference'         => $fund?->getReference() ?? null,
                                'fund_name'         => $fund?->getFundName() ?? null,
                                'no_of_shares'      => $fund?->getNoOfShares() ?? null,
                                'total_amount_ccy'  => $fund?->getTotalAmountCcy() ?? null, 
                                'total_amount_mur'  => $fund?->getTotalAmountMur() ?? null,
                                // 'nav_id'            => $entity->getId(),
                                'avg_nav'           => $entity?->getValue() ?? null,
                                'c_name'            => $entity?->getTypeNav() ?? null,
                                'nav'               => $entity?->getTypeNav() ?? null . ' ' . $entity?->getValue() ?? null,
                                'nav_date'          => $entity?->getNavDate()?->format('Y-m-d'),
                                'month_name'        => $entity?->getNavDate()?->format('F'),
                                'month_number'      => $entity?->getNavDate()?->format('m'),
                                'year'              => $entity?->getNavDate()?->format('Y'),
                                'year_month'        => $entity?->getNavDate()?->format('d-M-Y')
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
                        ->findByNameAndPeriod(intval($userId) ,$fundName, $startDate);

                // Liste des navs
                foreach ($funds as $entity) {
                    if ($entity instanceof \App\Entity\Scs\NavFunds) {
                        $fund = $entity?->getFundId() ?? null;
                        $fundId = $fund?->getId();
                        // --- Format pour TOUTES les NAVs ---
                        $allNavs[] = [
                            'id'                => $fund?->getId() ?? null,
                            'fund_id'           => $fundId,
                            'reference'         => $fund?->getReference() ?? null,
                            'fund_name'         => $fund?->getFundName() ?? null,
                            'no_of_shares'      => $fund?->getNoOfShares() ?? null,
                            'total_amount_ccy'  => $fund?->getTotalAmountCcy() ?? null,
                            'total_amount_mur'  => $fund?->getTotalAmountMur() ?? null,
                            'avg_nav'           => $entity?->getValue() ?? null,
                            'c_name'            => $entity?->getTypeNav() ?? null,
                            'nav'               => $entity?->getTypeNav() ?? null.' '.$entity?->getValue() ?? null,
                            'nav_date'          => $entity?->getNavDate()?->format('Y-m-d'),
                            'month_name'        => $entity?->getNavDate()?->format('F'),
                            'month_number'      => $entity?->getNavDate()?->format('m'),
                            'year'              => $entity?->getNavDate()?->format('Y'),
                            'year_month'        => $entity?->getNavDate()?->format('d-M-Y'),
                            'color'             => $this->generateColorFromString((string) $fundId) // couleur fixe
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
                    'id'        => $nav?->getId() ?? null,
                    'code_name' => $nav?->getType() ?? null,
                    'value'     => $nav?->getValue() ?? null
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
            $funds = $this->em->getRepository(Fund::class)->findByUserId(intval($userId));

            if (empty($funds)) {
                return $this->json([
                    'status'  => 'success',
                    'code'    => JsonResponse::HTTP_OK,
                    'message' => 'No funds found for this user.',
                    'data'    => null,
                ], JsonResponse::HTTP_OK);
            }

            // Récupérer le fundNav avec la date la plus récente
            $navsOnly = array_filter($funds, fn($f) => $f instanceof NavFunds);

            $lastNav = array_reduce($navsOnly, function ($carry, NavFunds $nav) {
                return ($carry === null || $nav?->getNavDate() > $carry?->getNavDate())
                    ? $nav
                    : $carry;
            });
            
            return $this->json([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful Nav and last valuation date.',
                'data'    => [
                    'nav_per_share'  => $lastNav?->getValue(),
                    'valuation_date' => $lastNav?->getNavDate()?->format('d M Y'),
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

    /**
     * Générer une couleur hexadécimale à partir d
     *  
     *  @param string $input
     *  @return string
     */
    private function generateColorFromString(string $input): string
    {
        // Hash (md5 par exemple)
        $hash = md5($input);

        // Prendre les 6 premiers caractères → couleur hex
        return '#' . substr($hash, 0, 6);
    }

}