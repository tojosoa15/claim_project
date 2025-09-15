<?php
namespace App\Controller;

use App\Repository\AccountInformationsRepository;
use App\Repository\FinancialInformationsRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EmploymentInformationRepository;
use App\Repository\DocumentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Doctrine\Persistence\ManagerRegistry;

#[AsController]
class ProfileController extends AbstractController
{
    public function getAllProfile(
        Request $request,
        AccountInformationsRepository $accountRepo,
        FinancialInformationsRepository $financialRepo,
        EmploymentInformationRepository $employRepo,
        DocumentRepository $document
    ): JsonResponse
    {
          $userId = $request->query->get('usersId'); // récupère ?usersId=5

            if (!$userId) {
                return new JsonResponse([
                    'status'  => 'error',
                    'code'    => JsonResponse::HTTP_BAD_REQUEST,
                    'message' => 'User ID is required'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        try {
            $account = $accountRepo->findByUserId($userId);
            $financial = $financialRepo->findByUserId($userId);
            $professional = $employRepo->findByUserId($userId);
            $document = $document->findByUserId($userId);

            if (!$account) {
                return new JsonResponse([
                    'status'  => 'error',
                    'code'    => JsonResponse::HTTP_NOT_FOUND,
                    'message' => 'User not found'
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            $documentsArray = array_map(function($doc) {
                return [
                    'name' => $doc->getName(),
                    'date' => $doc->getDate()->format('Y-m-d'),
                ];
            }, $document);

            $baseUrl = $request->getSchemeAndHttpHost();
            $profileImageUrl = $account->getProfileImage() 
                ? $baseUrl . '/uploads/profile/' . $account->getProfileImage() 
                : null;

            $data = [
                'personal_information' => [
                    'profile_image'          => $profileImageUrl,
                    'client_name'            => $account->getBusinessName(),
                    'date_of_birth'          => $account->getDateOfBirth()->format('Y-m-d'),
                    'nic'                    => $account->getNic(),
                    'Address'                => $account->getBusinessAddress(),
                    'country_of_nationality' => $account->getCountryOfNationality(),
                    'home_number'            => $account->getHomeNumber(),
                    'mobile_number'          => $account->getPhoneNumber(),
                    'email_address'          => $account->getEmailAddress(),
                    'kyc'                    => $account->getKyc()->format('Y-m-d'),
                ],
                'financial_information' =>[
                    'holder_name'           => $financial->getBankHolderName(),
                    'bank_name'             => $financial->getBankName(),
                    'bank_account_number'   => $financial->getBankAccountNumber(),
                    'bank_address'          => $financial->getBankAddress(),
                    'bank_country'          => $financial->getBankCountry(),
                ] ,
                'employment_information' => [
                    'present_occupation' => $professional->getPresentOccupation(),
                    'company_name'       => $professional->getCompanyName(),
                    'company_address'    => $professional->getCompanyAddress(),
                    'office_phone'       => $professional->getOfficePhone(),
                    'monthly_income'     => $professional->getMonthlyIncome(),
                ],
                'security_settings' => [
                    'password'     => $account->getPassword(),
                    'backup_email' => $account->getBackupEmail(),
                ],
                'documents' => $documentsArray
            ];

            return new JsonResponse([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful professional information.',
                'data'    => $data
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function uploadProfileImage(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $userId = $request->request->get('usersId');
        $file = $request->files->get('profile_image');

        if (!$userId || !$file) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'User ID and file are required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $em = $doctrine->getManager('claim_user_db');
        $accountRepo = $em->getRepository(\App\Entity\ClaimUser\AccountInformations::class);
        $account = $accountRepo->findByUserId($userId);

        if (!$account) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'User not found.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // Vérification type fichier
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid file type.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $newFilename = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getParameter('profile_images_directory'), $newFilename);
            $account->setProfileImage($newFilename);
            $em->persist($account);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $baseUrl = $request->getSchemeAndHttpHost();
        $imageUrl = $baseUrl . '/uploads/profile/' . $newFilename;

        return new JsonResponse([
            'status' => 'success',
            'profile_image' => $imageUrl
        ], JsonResponse::HTTP_OK);
    }

}
