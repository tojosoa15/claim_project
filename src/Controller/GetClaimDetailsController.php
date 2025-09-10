<?php

namespace App\Controller;

use App\Entity\ClaimUser\Notification;
use App\Entity\Surveyor\PictureOfDamageCar;
use App\Service\ClaimDetailsService;
use App\Service\EmailService;
use App\Service\NotificationService;
use App\Service\SummaryExportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

#[AsController]
class GetClaimDetailsController extends AbstractController
{
    private EntityManagerInterface $claimUserEm;

    public function __construct(
        private ClaimDetailsService $claimDetailsService,
        private EmailService $emailService,
        ManagerRegistry $doctrine,
        private NotificationService $notificationService,
        private SummaryExportService $summaryExportService
    ) {
        // On récupère l'EntityManager lié à claim_user_db
        $this->claimUserEm = $doctrine->getManager('claim_user_db');
    }

    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->query->all();

        if (empty($params['claimNo']) && empty($params['email'])) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_BAD_REQUEST,
                    'message'   => 'Claim Number and email parameters is required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        
        try {
            
            $results = $this->claimDetailsService->callGetClaimDetails([
                'p_claim_number'    => $params['claimNo'],
                'p_email'           => $params['email']
            ]);

            // foreach ($results as $res) {
            //     $resFormat = [
            //         'claim_number'  => $res['claim_number'],
            //         'status_name'   => $res['status_name'],
            //         'vehicle_informatin' => [
            //             'make'                      => $res['make'],
            //             'model'                     => $res['model'],
            //             'cc'                        => $res['cc'],
            //             'fuel_type'                 => $res['fuel_type'],
            //             'transmission'              => $res['transmission'],
            //             'engime_no'                 => $res['engime_no'],
            //             'chasisi_no'                => $res['chasisi_no'],
            //             'vehicle_no'                => $res['vehicle_no'],
            //             'color'                     => $res['color'],
            //             'odometer_reading'          => $res['odometer_reading'],
            //             'is_the_vehicle_total_loss' => $res['is_the_vehicle_total_loss'],
            //             'condition_of_vehicle'      => $res['condition_of_vehicle'],    
            //             'place_of_survey'           => $res['place_of_survey'],
            //             'point_of_impact'           => $res['point_of_impact']
            //         ],
            //         'survey_information' => [
            //             'garage'                => $res['garage'],
            //             'garage_address'        => $res['garage_address'],
            //             'garage_contact_number' => $res['garage_contact_number'],
            //             'eor_value'             => $res['eor_value'],
            //             'invoice_number'        => $res['invoice_number'],
            //             'survey_type'           => $res['survey_type'],
            //             'date_of_survey'        => $res['date_of_survey']
            //         ],
            //         'estimate_of_repairs' => [
            //             'part_detail'   =>  [
            //                 'part_name'     => $res['part_name'],
            //                 'quantity'      => $res['quantity'],
            //                 'supplier'      => $res['supplier'],
            //                 'quality'       => $res['quality'],
            //                 'cost_part'     => $res['cost_part'],
            //                 'discount_part' => $res['discount_part'],
            //                 'vat_part'      => $res['vat_part'],
            //                 'part_total'    => $res['part_total']
            //             ],
            //             'labour_detail' => [
            //                 'eor_or_surveyor'       => $res['eor_or_surveyor'],
            //                 'activity'              => $res['activity'],
            //                 'number_of_hours'       => $res['number_of_hours'],
            //                 'hourly_const_labour'   => $res['hourly_const_labour'],
            //                 'discount_labour'       => $res['discount_labour'],
            //                 'vat_labour'            => $res['vat_labour'],
            //                 'labour_total'          => $res['labour_total']
            //             ],
            //             'remarks'   => $res['remarks']
            //         ]
            //     ];
            // }

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful Claim details',
                'data'      => $results
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => 'Claim retrieval failed.'
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Renseignement verification surveyor
     * 
     * @param $request
     */
    public function surveyorReport(Request $request,  SluggerInterface $slugger, EntityManagerInterface $em): JsonResponse 
    {
        $data = $this->getRequestData($request);
        
        $recentStep = '';
        // $data       = (array)json_decode($request->getContent(), true);

        $params = [
            'claimNo'       => $data['claimNo'],
            'surveyorId'    => $data['surveyorId'],
            'status'        => $data['status'] ?? false,
            'currentStep'   => $data['currentStep'],
            'json_data'     => json_encode($data)
        ];
        $requiredFields = ['claimNo', 'surveyorId', 'currentStep'];
        
        foreach ($requiredFields as $field) {

            if (empty($data[$field]) && $data[$field] !== false && $data[$field] !== 0) {
                return new JsonResponse(
                    [
                        'status'    => 'error',
                        'code'      => JsonResponse::HTTP_BAD_REQUEST,
                        'message'   => "'{$field}' parameters is required"
                    ],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        // Validation champs obligatoires etape 1
        if ($data['currentStep'] === 'step_1') {
            $requiredVehicleInformation = [
                'make', 'model', 'cc', 'fuelType', 'transmission', 'engineNo',
                'chassisNo', 'vehicleNo', 'color', 'odometerReading',
                'isTotalLoss', 'conditionOfVehicle',
                'placeOfSurvey', 'pointOfImpact'
            ];

            foreach ($requiredVehicleInformation as $field) {
                if (empty($data[$field])) {
                    return new JsonResponse(
                        [
                            'status'  => 'error',
                            'code'    => JsonResponse::HTTP_BAD_REQUEST,
                            'message' => "The '{$field}' field is required for step 1."
                        ],
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            }

            $recentStep = 'Vehicle information';
        }

        // Validation champs obligatoires etape 2
        if ($data['currentStep'] === 'step_2') {
            $requiredSurveyInformation = [
                'garage', 'garageAddress', 'garageContactNumber', 'eorValue', 'invoiceNumber', 'surveyType',
                'dateOfSurvey', 'timeOfSurvey', 'preAccidentValue', 'showroomPrice','wreckValue', 'excessApplicable'
            ];

            foreach ($requiredSurveyInformation as $field) {
                if (empty($data[$field])) {
                    return new JsonResponse(
                        [
                            'status'  => 'error',
                            'code'    => JsonResponse::HTTP_BAD_REQUEST,
                            'message' => "The '{$field}' field is required for step 2."
                        ],
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            }

            $recentStep = 'Survey information';
        }

        // Validation champs obligatoires etape 3
        if ($data['currentStep'] === 'step_3') {
            $requiredEstimateFields = ['parts', 'labours', 'additionalLabours'];

            foreach ($requiredEstimateFields as $field) {
                if (empty($data[$field])) {
                    return new JsonResponse(
                        [
                            'status'  => 'error',
                            'code'    => JsonResponse::HTTP_BAD_REQUEST,
                            'message' => "The '{$field}' field is required for step 3."
                        ],
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            }

            // Valider que parts et labours sont des tableaux
            if (!is_array($data['parts']) || !is_array($data['labours'])) {
                return new JsonResponse(
                    ['status' => 'error', 'message' => 'The “parts” and “labours” fields must be tables.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $recentStep = 'Estimate of repair';
        }

        try {
            $results = $this->claimDetailsService->callSpVerificationProcessSurveyor([
                'p_claim_number'    => $params['claimNo'],
                'p_surveyor_id'     => $params['surveyorId'],
                'p_status'          => $params['status'],
                'p_current_step'    => $params['currentStep'],
                'p_json_data'       => $params['json_data']
            ]);
            
            if($params['currentStep'] === 'step_3' && is_array($results)) {
                foreach ($results as $res) {
                    $results = [
                        'claim_number'  => $res['claim_number'],
                        'status_name'   => $res['status_name'],
                        'general_information' => [
                            'name'                      => $res['name'],
                            'make'                      => $res['make'],
                            'model'                     => $res['model'],
                            'condition_of_vehicle'      => $res['condition_of_vehicle'],    
                            'chassis_no'                => $res['chasisi_no'],
                            'point_of_impact'           => $res['point_of_impact'],
                            'place_of_survey'           => $res['place_of_survey'],
                            'is_the_vehicle_total_loss' => $res['is_the_vehicle_total_loss']
                        ],
                        'survey_information' => [
                            'invoice_number'        => $res['invoice_number'],
                            'survey_type'           => $res['survey_type'],
                            'eor_value'             => $res['eor_value'],
                            'date_of_survey'        => $res['date_of_survey']
                        ],
                        'rapaire_estimate' => [
                            'part_details'   =>  [
                                'cost_part'     => $res['cost_part'],
                                'discount_part' => $res['discount_part'],
                                'vat_part'      => $res['vat_part'],
                                'part_total'    => $res['part_total']
                            ],
                            'labour_details' => [
                                'hourly_const_labour'   => $res['hourly_const_labour'],
                                'discount_labour'       => $res['discount_labour'],
                                'vat_labour'            => $res['vat_labour'],
                                'labour_total'          => $res['labour_total']
                            ],
                            'grand_total'   => [
                                'cost_total'        => $res['cost_total'],//(float)$res['cost_part'] + (float)$res['hourly_const_labour'],
                                'discount_total'    => $res['discount_total'],//(float)$res['discount_part'] + (float)$res['discount_labour'],
                                'vat_total'         => $res['vat_total'],//(float)$res['vat_part'] + (float)$res['vat_labour'],
                                'total'             => $res['total']//(float)$res['part_total'] + (float)$res['labour_total']
                            ],
                        ]
                    ];
                }
            }
            // Pour les insertions image télécharger
            // if ($data['currentStep'] === 'step_2') {
            //     $imageFiles = $request->files->get('imageFile');

            //     $qb = $em->createQuery(
            //         'SELECT s FROM App\Entity\Surveyor\SurveyInformation s 
            //         JOIN s.verification v 
            //         WHERE v.claimNumber = :claimNo'
            //     );
            //     $qb->setParameter('claimNo', $params['claimNo']);

            //     $survey = $qb->getOneOrNullResult();

            //     if (!$survey) {
            //         return new JsonResponse(['error' => 'Survey not found'], 404);
            //     }

            //     // Répertoire d'upload
            //     $uploadDir = 'D:' . DIRECTORY_SEPARATOR . 'Santatra' . DIRECTORY_SEPARATOR . 'Pictures' . DIRECTORY_SEPARATOR . 'Pictures' . DIRECTORY_SEPARATOR . 'testPictures';

            //     // Normaliser $imageFiles en tableau (même s'il n'y a qu'un seul fichier)
            //     if (!is_array($imageFiles)) {
            //         $imageFiles = [$imageFiles];
            //     }

            //     foreach ($imageFiles as $imageFile) {
            //         if ($imageFile && $imageFile->isValid()) {
            //             $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            //             $safeFilename = $slugger->slug($originalFilename);
            //             $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            //             try {
            //                 $imageFile->move($uploadDir, $newFilename);

            //                 $picture = new PictureOfDamageCar();
            //                 $picture->setPath($uploadDir . DIRECTORY_SEPARATOR . $newFilename);
            //                 $picture->setSurveyInformation($survey);
            //                 $picture->setDeletedAt(null);

            //                 $em->persist($picture);
            //             } catch (FileException $e) {
            //                 return new JsonResponse([
            //                     'error' => 'Upload failed for ' . $originalFilename,
            //                     'details' => $e->getMessage()
            //                 ], 500);
            //             }
            //         }
            //     }

            //     $em->flush();
            // }

            return new JsonResponse([
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => "{$recentStep} successfully completed",
                    'data'      => $results
                
            ]);

        } catch (\Throwable $th) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => 'Claim retrieval failed.'
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retourne le résumé des rapports vérificateurs
     * 
     * @param $request
     */
    public function reportSummary(Request $request): JsonResponse
    {
        $resFormat = [];

        $params = $request->query->all();

        if (empty($params['claimNo']) && empty($params['email'])) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_BAD_REQUEST,
                    'message'   => 'Claim Number and email parameters is required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $results = $this->claimDetailsService->callGetSummary([
                'p_claim_number'    => $params['claimNo'],
                'p_email'           => $params['email']
            ]);
            

            foreach ($results as $res) {
                $resFormat = [
                    'claim_number'  => $res['claim_number'],
                    'status_name'   => $res['status_name'],
                    'general_information' => [
                        'name'                      => $res['name'],
                        'make'                      => $res['make'],
                        'model'                     => $res['model'],
                        'condition_of_vehicle'      => $res['condition_of_vehicle'],    
                        'chasisi_no'                => $res['chasisi_no'],
                        'point_of_impact'           => $res['point_of_impact'],
                        'place_of_survey'           => $res['place_of_survey'],
                        'is_the_vehicle_total_loss' => $res['is_the_vehicle_total_loss']
                    ],
                    'survey_information' => [
                        'invoice_number'        => $res['invoice_number'],
                        'survey_type'           => $res['survey_type'],
                        'eor_value'             => $res['eor_value'],
                        'date_of_survey'        => $res['date_of_survey']
                    ],
                    'rapaire_estimate' => [
                        'part_details'   =>  [
                            'cost_part'     => $res['cost_part'],
                            'discount_part' => $res['discount_part'],
                            'vat_part'      => $res['vat_part'],
                            'part_total'    => $res['part_total']
                        ],
                        'labour_details' => [
                            'hourly_const_labour'   => $res['hourly_const_labour'],
                            'discount_labour'       => $res['discount_labour'],
                            'vat_labour'            => $res['vat_labour'],
                            'labour_total'          => $res['labour_total']
                        ],
                        'grand_tatal'   => [
                            'cost_total'        => $res['cost_total'],//(float)$res['cost_part'] + (float)$res['hourly_const_labour'],
                            'discount_total'    => $res['discount_total'],//(float)$res['discount_part'] + (float)$res['discount_labour'],
                            'vat_total'         => $res['vat_total'],//(float)$res['vat_part'] + (float)$res['vat_labour'],
                            'total'             => $res['total']//(float)$res['part_total'] + (float)$res['labour_total']
                        ],
                    ]
                ];
            }

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful report summary',
                'data'      => $resFormat
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => 'Claim retrieval failed.'
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Export en pdf du résumé de la vérification
     * 
     * @param $request 
     */
    public function reportSummaryExportPdf(Request $request) {
        $params = $request->query->all();
        

        if (empty($params['claimNo']) && empty($params['email'])) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_BAD_REQUEST,
                    'message'   => 'Claim Number and email parameters is required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $results = $this->claimDetailsService->callGetSummary([
                'p_claim_number'    => $params['claimNo'],
                'p_email'           => $params['email']
            ]);

            return $this->summaryExportService->generatePdf($results);

            throw new \Exception('Type d\'export pas renseigné');

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => 'Claim retrieval failed.'
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Envoyer par mail le ficiher résumé de la vérification en pdf
     * 
     * @param $request 
     */
    public function reportSummarySendMail(Request $request) {
        $params =  (array)json_decode($request->getContent(), true);
        $email      = $params['email'];
        $claimNo    = $params['claimNo'];
        
        if (empty($claimNo) && empty($email)) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_BAD_REQUEST,
                    'message'   => 'Claim Number and email parameters is required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        
        try {
            $results = $this->claimDetailsService->callGetSummary([
                'p_claim_number'    => $claimNo,
                'p_email'           => $email
            ]);
         
            // Générer le PDF et le sauvegarder temporairement
            $pdfFilePath = $this->summaryExportService->generatePdfToFile($results);
   
            // $response = $this->sendMailAndNotification($email, $pdfFilePath, $claimNo);
            $this->sendMailAndNotification($email, $pdfFilePath, $claimNo);

            // if (!$response) {
            //     return new JsonResponse(
            //         [
            //             'status'    => 'error',
            //             'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            //             'message'   => 'Failed to send email or create notification.'
            //         ],
            //         JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            //     );
            // }

            return new JsonResponse([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Email sent with PDF attachment and create a successful notification.'
            ], JsonResponse::HTTP_OK);


        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Claim retrieval or email sending failed.',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Envoi de l'email et création de la notification
     * 
     * @param string $email
     * @param string $pdfFilePath
     * @param string $claimNo
     */
    public function sendMailAndNotification(string $email, string $pdfFilePath, string $claimNo)
    {
        try {
            // Envoi de l'email avec le PDF en pièce jointe
            $this->emailService->sendSummaryWithAttachment($email, $pdfFilePath);

            $claimId = $this->claimUserEm->createQuery(
                'SELECT c.id FROM App\Entity\ClaimUser\Claims c WHERE c.number = :claimNo'
            )
            ->setParameter('claimNo', $claimNo)
            ->getSingleScalarResult();

            $userId = $this->claimUserEm->createQuery(
                'SELECT u.id 
                FROM App\Entity\ClaimUser\AccountInformations ai 
                JOIN ai.users u
                WHERE ai.emailAddress = :email_address'
            )
            ->setParameter('email_address', $email)
            ->getSingleScalarResult();

            // Récupération des objets
            $claim = $this->claimUserEm->getReference(\App\Entity\ClaimUser\Claims::class, $claimId);
            $user  = $this->claimUserEm->getReference(\App\Entity\ClaimUser\Users::class, $userId);

            // Création de la notification
            $notification = new Notification();
            $notification->setUsers($user); // objet Users
            $notification->setClaims($claim); // objet Claims
            $notification->setChannel('portal');
            $notification->setType('claim_summary');
            $notification->setContent("A summary report for claim number {$claimNo} has been sent to your email.");
            $notification->setClaimNumber($claimNo);

            // Envoi de la notification via le service
            $this->notificationService->sendNotification($notification);

        } catch (\Exception $e) {
            // Log the error if needed
            return false;
        }
    }

    /**
     * Retourne le total de pièce et main d'oeuvre
     * 
     * @param $request
     */
    function getReportTotalPartOrLabour(Request $request): JsonResponse
    {
        $params = $request->query->all();

        if (empty($params['claimNo']) && empty($params['email'])) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_BAD_REQUEST,
                    'message'   => 'Claim Number and email parameters is required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $results = $this->claimDetailsService->callGetTotalReport([
                'p_claim_number'    => $params['claimNo'],
                'p_email'           => $params['email'],
                'p_section'         => $params['section'] ?? null
            ]);

            return new JsonResponse([
                'status'    => 'success',
                'code'      => JsonResponse::HTTP_OK,
                'message'   => 'Successful report total part or labour',
                'data'      => $results
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status'    => 'error',
                    'code'      => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message'   => 'Claim retrieval failed.'
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function getRequestData(Request $request): array
    {
        $data = $request->request->all();

        if (empty($data)) {
            $json = json_decode($request->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $data = $json;
            }
        }

        return $data;
    }
}