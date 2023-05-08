<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Video;
use App\Form\VideoType;
use App\Traits\EntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    use EntityManagerTrait;

    #[Route('/{path<(?!api).*>}', name: 'app_home')]
    public function index(?string $path, Request $request): Response
    {
        return $this->render('base.html.twig');
    }
}
