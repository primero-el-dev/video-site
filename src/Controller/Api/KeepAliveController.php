<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class KeepAliveController extends AbstractController
{
    #[Route('/api/keep-alive', name: 'api_keep_alive')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Session was renewed successfully.',
        ]);
    }
}
