<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Exception\ApiException;
use App\Traits\TranslatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    use TranslatorTrait;

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

    #[ParamConverter('id', class: User::class)]
    #[Route('/{id}', name: 'api_user_show', methods: 'GET')]
    public function show(User $user): JsonResponse
    {
        return $this->json(
            [
                'data' => $user,
            ],
            context: ['groups' => ['user_extended']]
        );
    }

    #[ParamConverter('id', class: User::class)]
    #[Route('/{id}/subscribe', name: 'api_user_subscribe', methods: 'POST')]
    public function subscribe(User $user): JsonResponse
    {
        if ($user->getId() === $this->getUser()->getId()) {
            throw new ApiException(
                $this->translator->trans('controller.user.subscribe.cannotSubscribeYourself')
            );
        }

        // try {

        // }

        return $this->json(
            [
                'data' => $this->getUser(),
            ],
            context: ['groups' => ['user_full', 'video']]
        );
    }
}
