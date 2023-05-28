<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use App\Traits\TranslatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tag')]
class TagController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use TranslatorTrait;

    public function __construct(
        private TagRepository $tagRepository,
    ) {}

    #[Route('/', name: 'api_tag_index', methods: 'GET')]
    public function index(Request $request): JsonResponse
    {
        return $this->json(
            [
                'data' => $this->tagRepository->findAll(),
            ],
            context: ['groups' => ['tag']]
        );
    }

    #[ParamConverter('tag', class: Tag::class)]
    #[Route('/{id}', name: 'api_tag_show', methods: 'GET')]
    public function show(Tag $tag, Request $request): JsonResponse
    {
        return $this->json(
            [
                'data' => $tag,
            ],
            context: ['groups' => ['tag_extended']]
        );
    }
}
