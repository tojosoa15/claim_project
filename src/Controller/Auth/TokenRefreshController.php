<?php

namespace App\Controller\Auth;

use App\Entity\Surveyor\RefreshTokens;
use App\Service\MercureTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\Persistence\ManagerRegistry;

class TokenRefreshController
{
    private EntityManagerInterface $em;
    private EntityManagerInterface $claimUserEm;
    private JWTTokenManagerInterface $jwtManager;
    private UserProviderInterface $userProvider;
    private MercureTokenGenerator $mercureTokenGenerator;
    

    public function __construct(
        EntityManagerInterface $em,
        EntityManagerInterface $claimUserEm,
        JWTTokenManagerInterface $jwtManager,
        UserProviderInterface $userProvider,
        MercureTokenGenerator $mercureTokenGenerator,
        ManagerRegistry $registry
    ) {
        $this->em                       = $em;
        $this->claimUserEm              = $registry->getManager('claim_user_db');
        $this->jwtManager               = $jwtManager;
        $this->userProvider             = $userProvider;
        $this->mercureTokenGenerator    = $mercureTokenGenerator;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data           = $request->toArray();
        $refreshToken   = $data['refresh_tokens'] ?? null;

        if (!$refreshToken) {
            return new JsonResponse(['message' => 'Paramètre `refresh_tokens` manquant.'], 401);
        }

        $refreshTokenObj = $this->em->getRepository(RefreshTokens::class)->find($refreshToken);

        if (!$refreshTokenObj || !$refreshTokenObj->isValid()) {
            return new JsonResponse(['message' => 'Invalid or expired refresh token'], 400);
        }
        
        // Optionnel : renouveler le refresh token (ici, on le garde identique)
       $user = $this->claimUserEm->getRepository(\App\Entity\ClaimUser\AccountInformations::class)
                ->findOneBy(['emailAddress' => $refreshTokenObj->getUsername()]);

        $accessToken = $this->jwtManager->create($user);


        // Token JWT pour Mercure
        $mercureToken = $this->mercureTokenGenerator->generateToken(
                subscribe: ["*"],   // ou "https://example.com/user/{$user->getId()}"
                publish: []         // vide si l’utilisateur ne peut pas publier
        );

        return new JsonResponse([
            'status'    => 'success',
            'code'      => 200,  
            'message'   => 'Token refreshed successfully.',
            'data'      => [ 
                'accessToken'   => $accessToken,
                'refreshToken'  => $refreshToken,
                'mercureToken'  => $mercureToken,
                'user' => [
                    'id'            => $user->getId(),
                    'email'         => $user->getEmailAddress(),
                    'roles'         => $user->getRoles(),
                    'business_name' => $user->getBusinessName(),
                ]
            ]
        ]);
    }
}