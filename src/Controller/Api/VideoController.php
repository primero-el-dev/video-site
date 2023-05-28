<?php

namespace App\Controller\Api;

use App\Controller\Api\Traits\ValidateProcessAndReturnExistingTrait;
use App\Entity\Enum\UserActionEnum;
use App\Entity\UserView\UserVideoView;
use App\Entity\Video;
use App\Exception\ApiException;
use App\Form\VideoType;
use App\Messenger\Command\SendAppNotificationCommand;
use App\Repository\UserSubjectAction\UserVideoActionRepository;
use App\Repository\VideoRepository;
use App\Security\Voter\VideoVoter;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use App\Traits\NormalizeWithVoterPermissionsTrait;
use App\Traits\TranslatorTrait;
use App\Util\FileHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/video')]
class VideoController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use NormalizeWithVoterPermissionsTrait;
    use TranslatorTrait;
    use ValidateProcessAndReturnExistingTrait;

    public function __construct(
        private VideoRepository $videoRepository,
        private FileHandlerInterface $fileHandler,
        private ValidatorInterface $validator,
        private UserVideoActionRepository $userVideoActionRepository,
        private MessageBusInterface $commandBus,
    ) {}

    #[Route('/', name: 'api_video_index', methods: 'GET')]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'data' => $this->normalizeWithVoterPermissions(
                $this->videoRepository->findByConstraints($request->query->all()), 
                new VideoVoter(), 
                ['groups' => ['video', 'user', 'tag']]
            ),
        ]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}', name: 'api_video_show', methods: 'GET')]
    public function show(Video $video, Request $request): JsonResponse
    {
        $data = $this->getNormalizedExtendedVideoWithPermissions($video);
        $userVideoView = (new UserVideoView())->setSubject($video);

        if ($this->getUser()) {
            $data['currentUserRating'] = $video->getUserRating($this->getUser());
            $userVideoView->setUser($this->getUser());
        } else {
            $userIp = $request->server->get('HTTP_CF_CONNECTING_IP') ?? $request->getClientIp();
            $userVideoView->setIp($userIp);
        }

        $errors = $this->validator->validate($userVideoView);
        if (!count($errors)) {
            $this->em->persist($userVideoView);
            $this->em->flush();
        }
        
        return $this->json([
            'data' => $data,
        ]);
    }

    #[Route('/', name: 'api_video_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('USER_VERIFIED');
        
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);

        // $form->handleRequest($request) haven't worked properly
        $form->submit(array_merge($request->request->all(), $request->files->all()));
        if ($form->isValid()) {
            $video->setSnapshotPath($this->fileHandler->uploadImage($form->get('snapshot')->getData()));
            $video->setVideoPath($this->fileHandler->uploadVideo($form->get('video')->getData()));
            $video->setOwner($this->getUser());

            $this->em->persist($video);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $this->getUser(),
                UserActionEnum::VIDEO_CREATE, 
                $video
            ));

            return $this->json([
                'message' => $this->translator->trans('controller.video.create.success'),
                'data' => $this->getNormalizedExtendedVideoWithPermissions($video),
            ]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    // Why 'POST'? https://github.com/symfony/symfony/issues/9226
    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/update', name: 'api_video_update', methods: 'POST')]
    public function update(Video $video, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(VideoVoter::UPDATE, $video);

        $form = $this->createForm(VideoType::class, $video, ['require_video' => false]);

        // $form->handleRequest($request) haven't worked properly
        $form->submit(array_merge($request->request->all(), $request->files->all()));
        if ($form->isValid()) {
            if ($newVideo = $form->get('video')->getData()) {
                $oldVideoPath = $video->getVideoPath();
                $video->setVideoPath($this->fileHandler->uploadVideo($newVideo));
                $this->fileHandler->deleteVideo($oldVideoPath);
            }
            $oldSnapshotPath = $video->getSnapshotPath();
            $video->setSnapshotPath($this->fileHandler->uploadImage($form->get('snapshot')->getData()));
            $this->fileHandler->deleteImage($oldSnapshotPath);
            
            $this->em->persist($video);
            $this->em->flush();

            return $this->json([
                'message' => $this->translator->trans('controller.video.update.success'),
                'data' => $this->getNormalizedExtendedVideoWithPermissions($video),
            ]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}', name: 'api_video_delete', methods: 'DELETE')]
    public function delete(Video $video, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(VideoVoter::DELETE, $video);

        $this->em->remove($video);
        
        $this->fileHandler->deleteVideo($video->getVideoPath());
        $this->fileHandler->deleteImage($video->getSnapshotPath());

        $this->commandBus->dispatch(new SendAppNotificationCommand(
            $this->getUser(),
            UserActionEnum::VIDEO_DELETE, 
            $video
        ));

        $this->em->flush();
        
        return $this->json([
            'message' => $this->translator->trans('controller.video.delete.success'),
            'data' => $this->getNormalizedExtendedVideoWithPermissions($video),
        ]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/{rate<(like)|(dislike)>}', name: 'api_video_rate', methods: 'POST')]
    public function rate(Video $video, string $rate, Request $request): JsonResponse
    {
        $entity = $this->validateProcessAndReturnExisting(
            UserActionEnum::from($rate), 
            $this->getUser(),
            $video,
            $request, 
        );

        return $this->json([
            'data' => $entity?->getAction(),
            'message' => $this->translator->trans('controller.video.rate.success'),
        ]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/report', name: 'api_video_report', methods: 'POST')]
    public function report(Video $video, Request $request): JsonResponse
    {
        $entity = $this->validateProcessAndReturnExisting(
            UserActionEnum::REPORT, 
            $this->getUser(),
            $video,
            $request, 
            ['reason' => $this->getUserActionReasonConstraints()], 
        );

        return $this->json([
            'data' => $entity?->getAction(),
            'message' => $this->translator->trans('controller.video.report.success'),
        ]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/report', name: 'api_video_report_delete', methods: 'DELETE')]
    public function reportDelete(Video $video, Request $request): JsonResponse
    {
        $this->userVideoActionRepository->deleteBy([
            'subject' => $video, 
            'user' => $this->getUser(),
            'action' => UserActionEnum::REPORT,
        ]);
        
        return $this->json([
            'data' => null,
            'message' => $this->translator->trans('controller.video.reportDelete.success'),
        ]);
    }

    /**
     * @return Constraint[]
     */
    private function getUserActionReasonConstraints(): array
    {
        return [
            new Assert\NotBlank(message: 'entity.userVideoAction.additionalInfo.reason.notBlank'),
            new Assert\Length(
                max: 255,
                maxMessage: 'entity.userVideoAction.additionalInfo.reason.length.max',
            ),
        ];
    }

    private function getNormalizedExtendedVideoWithPermissions(Video $video): array
    {
        return $this->normalizeWithVoterPermissions(
            $video, 
            new VideoVoter(), 
            ['groups' => ['video_extended', 'video_user', 'user', 'tag', 'rating', 'views', 'user_action', 'additional_data']],
            [
                'currentUserRating' => ['getUserRating', [$this->getUser()]],
                'currentUserActions' => ['getUserActions', [$this->getUser()]],
            ]
        );
    }
}
