<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExternalApiController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function __invoke(): JsonResponse
    {
        try {
            $response = $this->client->request('GET', $_ENV['EXTERNAL_API_URL'], [
                'headers' => [
                    'Authorization' => 'Bearer '.$_ENV['EXTERNAL_API_TOKEN'],
                ],
            ]);

            $data = $response->toArray();

            return $this->json($data); // API Platform gère le formatage
        } catch (\Exception $e) {
            return $this->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}