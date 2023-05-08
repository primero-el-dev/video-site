<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Video;
use App\Form\VideoType;
use App\Security\PermissionManagerInterface;
use App\Security\Voter\VideoVoter;
use App\Traits\EntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    use EntityManagerTrait;

    public function __construct(private PermissionManagerInterface $permissionManager) {}

    #[Route('/api-test', name: 'app_test')]
    public function test(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            dd($request->files->get('snapshot'));
        }

        $video = new Video();
        $video->addTag((new Tag())->setName('fourth'));
        $form = $this->createForm(VideoType::class, $video);

        return $this->render('test.html.twig', [
            'tags' => $this->em->getRepository(Tag::class)->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/apitest2', name: 'app_test2')]
    public function test2(Request $request): Response
    {
        return $this->render('test2.html.twig', [
            'videos' => $this->em->getRepository(Video::class)->findAll(),
        ]);
    }

    #[Route('/apitest3', name: 'app_test3')]
    public function test3(Request $request): Response
    {
        $video = $this->em->getRepository(Video::class)->findAll()[0];
        $granted = $this->permissionManager->filterGranted($video, VideoVoter::PERMISSIONS);

        dd($granted);
        return $this->render('test3.html.twig', [

        ]);
    }

    #[Route('/apitest4', name: 'app_test4')]
    public function test4(Request $request): Response
    {
        // $video = $this->em->getRepository(Video::class)->findAll()[0];
        // $granted = $this->permissionManager->filterGranted($video, VideoVoter::PERMISSIONS);

        // dd($granted);
        return $this->render('test4.html.twig', [

        ]);
    }
}
