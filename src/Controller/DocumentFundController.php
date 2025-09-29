<?php

namespace App\Controller;

use App\Entity\Scs\DocumentFund;
use App\Repository\DocumentFundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class DocumentFundController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        ManagerRegistry $doctrine,
    ) {
        $this->em = $doctrine->getManager('scs_db');
    }

    
    /**
     * Get documents by category
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentByCategory(Request $request) : JsonResponse {
        $category   = $request->query->get('slug');
        
        if (!$category) {
            return $this->json([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_BAD_REQUEST,
                'message' => 'Slug parameter is required',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

       $slugToCategoryId = [
            'statements'       => 1,
            'factsheets'       => 2,
            'contract-notes'   => 3,
            'dividend-notices' => 4,
        ];

        if (!isset($slugToCategoryId[$category])) {
            return $this->json([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_BAD_REQUEST,
                'message' => 'Invalid slug parameter',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $categoryId = $slugToCategoryId[$category];

        try {
            $params = $request->query->all();

            // Si plusieurs noms de fonds sont passés en paramètre, on les transforme en tableau
            if (!empty($params['searchFundName']) && is_string($params['searchFundName'])) {
                $params['searchFundName'] = array_map('trim', explode(',', $params['searchFundName']));
            }

            $documents = $this->em->getRepository(DocumentFund::class)->findByCategory($categoryId, $params);

            if (empty($documents)) {
                return $this->json([
                    'status'  => 'success',
                    'code'    => JsonResponse::HTTP_OK,
                    'message' => 'No documents found for this category.',
                    'data'    => null,
                ], JsonResponse::HTTP_OK);
            }

            // On formate les données comme tu veux
            $data = array_map(function (DocumentFund $doc) {
                return [
                    'id'         => $doc->getId(),
                    'name'       => $doc->getDocName(),
                    'path'       => $doc->getPath(),
                    'fund_name'  => $doc->getFundId() ? $doc->getFundId()->getFundName() : null,
                    'category'   => $doc->getCategoryId() ? $doc->getCategoryId()->getCategoryName() : null,
                    'date'       => $doc->getCreatedAt()?->format('d-M-Y'),
                ];
            }, $documents);

            return $this->json([
                'status'  => 'success',
                'code'    => JsonResponse::HTTP_OK,
                'message' => 'Successful Document list.',
                'data'    => $data,
            ], JsonResponse::HTTP_OK);

        } catch  (\Exception $e) {
            return $this->json([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * View user documents
     * 
     * @param Request $request
     * @param DocumentFundRepository $documentRepo
     */
    public function viewFundDocuments(Request $request, DocumentFundRepository $documentRepo): JsonResponse
    {
        $documentId = $request->query->get('documentId');

        if (!$documentId) {
            return new JsonResponse([
                'status'  => 'error',
                'code'    => JsonResponse::HTTP_BAD_REQUEST,
                'message' => 'documentId ID is required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // $documents = $documentRepo->findDocumentById($documentId);
        $document = $documentRepo->find((int)$documentId);

        $documentsArray = [];

        $documentsArray[] = [
            'id'       => $document->getId(),
            'view_url' => sprintf(
                '%s/uploads/documents/%s',
                $request->getSchemeAndHttpHost(),
                $document->getDocName()
            )
        ];

        return new JsonResponse([
            'status'    => 'success',
            'code'      => JsonResponse::HTTP_OK,
            'documents' => $documentsArray
        ], JsonResponse::HTTP_OK);
    }
}