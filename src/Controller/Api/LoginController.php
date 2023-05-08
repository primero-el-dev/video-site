<?php

namespace App\Controller\Api;

use App\Traits\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Exception\ApiException;

class LoginController extends AbstractController
{
    use TranslatorTrait;
    
    public function __construct(private Security $security) {}

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        if ($this->getUser()) {
            throw new ApiException($this->translator->trans('controller.login.login.alreadyLogged'), 403);
        }

        return $this->json([
            'message' => 'Welcome to your new controller!',
        ]);
    }
    
    #[Route('/api/logout', name: 'api_logout', methods: ['GET', 'POST'])]
    public function logout(): JsonResponse
    {
        $this->security->logout(false);

        return $this->json(['message' => $this->translator->trans('controller.login.logout.success')]);
    }
}
