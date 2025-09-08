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
    public function getAllNavFunds(Request $request): JsonResponse
    {
        $navFundsFormat = [];

        try {
            $navFunds = $this->em->getRepository(NavFunds::class)->findAll();

            foreach ($navFunds as $nav) {
                $navFundsFormat[] = [
                    'id'        => $nav->getId(),
                    'codeName'  => $nav->getCodeName(),
                    'typeNav'   => $nav->getTypeNav(),
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
                    'status' =>  'error',
                    'code'  => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
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
    public function getAllFundsByCustomer(Request $request): JsonResponse
    {
        $fundFormat = [];
        $lastDate   = null;
        $params     = $request->query->all();
        $fundName   = $params['fundName'] ?? null;    
        $period     = $params['period'] ?? null;

        try {
            // Aucun paramètre renseigné -> retourner tout
            if ((empty($fundName) || $fundName === null) && (empty($period) || $period === null)) {
                $funds = $this->em->getRepository(Fund::class)->findAll();
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

                // dd($startDate);

                $funds = $this->em->getRepository(Fund::class)
                        ->findByNameAndPeriod($fundName, $startDate);
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

                    // Calcul de la dernière date
                    if ($lastDate === null || $dateObj > $lastDate) {
                        $lastDate = clone $dateObj;
                    }
                } else {
                    $monthNameFr = $monthNumber = $year = $yearMonth = null;
                }

                $fundFormat[] = [
                    'id'            => $f->getId(),
                    'reference'     => $f->getReference(),
                    'fundName'      => $f->getFundName(),
                    'noOfShares'    => $f->getNoOfShares(),
                    'nav'           => $f->getNav(),
                    'totalAmountCcy'=> $f->getTotalAmountCcy(),
                    'fundDate'      => $fundDate ? $fundDate->format('Y-m-d') : null,
                    'avgNav'        => $avgNav,
                    'cName'         => $cName,
                    'monthName'     => $monthNameFr,
                    'monthNumber'   => $monthNumber,
                    'year'          => $year,
                    'yearMonth'     => $yearMonth
                ];
            }

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful list of funds by customer.',
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
    public function getForexRates(Request $request): JsonResponse
    {
        $forexRateFormat = [];

        try {
            $forexRate = $this->em->getRepository(ForexRate::class)->findAll();

            foreach ($forexRate as $nav) {
                $forexRateFormat[] = [
                    'id'        => $nav->getId(),
                    'codeName'  => $nav->getType(),
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
                    'status' =>  'error',
                    'code'  => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $e->getMessage()
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

}