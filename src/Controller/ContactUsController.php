<?php

namespace App\Controller;

use App\Repository\BusinessDevelopmentContactRepository;
use App\Repository\SwanCentreContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class ContactUsController extends AbstractController{
    
/**
 * Liste tous les contacts (Business Development + Swan Centre)
 *
 * @param Request $request
 * @param BusinessDevelopmentContactRepository $bdRepo
 * @param SwanCentreContactRepository $scRepo
 * @return JsonResponse
 */
public function getAllContact(
    BusinessDevelopmentContactRepository $bdRepo,
    SwanCentreContactRepository $scRepo
): JsonResponse {

    try {
        // Récupère le premier contact de chaque table
        $bdContact = $bdRepo->findFirstContact();
        $scContact = $scRepo->findFirstContact();

        $bdContactFormat = $bdContact ? [
            'name'     => $bdContact->getName(),
            'email'    => $bdContact->getEmail(),
            'phone'    => $bdContact->getPhone(),
            'mobile'   => $bdContact->getPortable()
        ] : null;

        $scContactFormat = $scContact ? [
            'address' => $scContact->getAddress(),
            'email'   => $scContact->getEmail(),
            'phone'   => $scContact->getPhone()
        ] : null;

        return new JsonResponse([
            'status'  => 'success',
            'code'    => JsonResponse::HTTP_OK,
            'message' => 'Successful list of contacts.',
            'data'    => [
                'business_development_contact_details' => $bdContactFormat,
                'swan_centre_contact_details'          => $scContactFormat
            ]
        ], JsonResponse::HTTP_OK);

    } catch (\Exception $e) {
        return new JsonResponse([
            'status'  => 'error',
            'code'    => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $e->getMessage()
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}
