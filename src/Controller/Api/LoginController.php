<?php

namespace App\Controller\Api;

use App\Traits\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ApiException;

class LoginController extends AbstractController
{
    use TranslatorTrait;

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        if ($this->getUser()) {
            throw new ApiException($this->translator->trans('controller.login.login.alreadyLogged'), 403);
        }

        return $this->json(null);
    }
    
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json(null);
    }
}
