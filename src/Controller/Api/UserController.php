<?php

namespace App\Controller\Api;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[ParamConverter('id', class: User::class)]
    #[Route('/{id<\d+>}', name: 'api_user_show', methods: 'GET')]
    public function show(User $user): JsonResponse
    {
        return $this->json(
            [
                'data' => $user,
            ],
            context: ['groups' => ['user_extended', 'video']]
        );
    }

    #[Route('/me', name: 'api_user_me', methods: 'GET')]
    public function me(): JsonResponse
    {
        return $this->json(
            [
                'data' => $this->getUser(),
            ],
            context: ['groups' => ['user_full', 'video']]
        );
    }
}
