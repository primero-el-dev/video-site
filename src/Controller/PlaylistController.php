<?php

namespace App\Controller;

use App\Entity\Enum\UserActionEnum;
use App\Entity\Playlist;
use App\Entity\PlaylistItem;
use App\Exception\ApiException;
use App\Form\PlaylistType;
use App\Messenger\Command\SendAppNotificationCommand;
use App\Security\Voter\PlaylistVoter;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use App\Traits\NormalizeWithVoterPermissionsTrait;
use App\Traits\TranslatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playlist')]
class PlaylistController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use NormalizeWithVoterPermissionsTrait;
    use TranslatorTrait;

    public function __construct(private MessageBusInterface $commandBus) {}

    #[Route('/', name: 'api_playlist_index', methods: 'GET')]
    public function index(): JsonResponse
    {


        return $this->json([]);
    }

    #[Route('/', name: 'api_playlist_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('USER_VERIFIED');

        $content = $this->getJsonContentOrThrowApiException($request);
        
        $playlist = new Playlist();
        $form = $this->createForm(PlaylistType::class, $playlist);

        // $form->handleRequest($request) haven't worked properly
        $form->submit($content);
        if ($form->isValid()) {
            $i = 0;
            foreach ($form->get('videos')->getData() as $video) {
                $playlistItem = (new PlaylistItem())
                    ->setVideo($video)
                    ->setOrder(++$i);
                $playlist->addPlaylistItem($playlistItem);
            }
            $playlist->setOwner($this->getUser());

            $this->em->persist($playlist);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $this->getUser(),
                UserActionEnum::PLAYLIST_CREATE,
                $playlist
            ));

            return $this->json([
                'message' => $this->translator->trans('controller.playlist.create.success'),
                'data' => $this->getNormalizedPlaylistWithPermissions($playlist),
            ]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('playlist', class: Playlist::class)]
    #[Route('/{id}', name: 'api_playlist_update', methods: 'PUT')]
    public function update(Playlist $playlist, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(PlaylistVoter::UPDATE, $playlist);

        $content = $this->getJsonContentOrThrowApiException($request);
        
        $form = $this->createForm(PlaylistType::class, $playlist);

        // $form->handleRequest($request) haven't worked properly
        $form->submit($content);
        if ($form->isValid()) {
            // Fix bug with orphan removal
            $playlistItems = [];
            $i = 0;
            foreach ($form->get('videos')->getData() as $video) {
                $playlistItems[] = (new PlaylistItem())
                    ->setVideo($video)
                    ->setOrder(++$i);
            }
            $playlist->removeAllPlaylistItems();
            $playlist->setPlaylistItems($playlistItems);

            $this->em->persist($playlist);
            $this->em->flush();

            return $this->json([
                'message' => $this->translator->trans('controller.playlist.update.success'),
                'data' => $this->getNormalizedPlaylistWithPermissions($playlist),
            ]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('playlist', class: Playlist::class)]
    #[Route('/{id}', name: 'api_playlist_delete', methods: 'DELETE')]
    public function delete(Playlist $playlist, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(PlaylistVoter::DELETE, $playlist);
    
        $this->em->remove($playlist);

        $this->commandBus->dispatch(new SendAppNotificationCommand(
            $this->getUser(),
            UserActionEnum::PLAYLIST_DELETE,
            $playlist
        ));

        $this->em->flush();

        return $this->json([
            'message' => $this->translator->trans('controller.playlist.delete.success'),
            'data' => $this->getNormalizedPlaylistWithPermissions($playlist),
        ]);
    }

    private function getNormalizedPlaylistWithPermissions(Playlist|array $playlist): array
    {
        return $this->normalizeWithVoterPermissions(
            $playlist, 
            new PlaylistVoter(), 
            ['groups' => ['playlist', 'playlist_items', 'playlist_item', 'video', 'user']],
        );
    }
}
