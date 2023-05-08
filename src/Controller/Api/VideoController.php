<?php

namespace App\Controller\Api;

use App\Controller\Api\Traits\ValidateSaveUserActionAndReturnResponseTrait;
use App\Entity\Enum\UserActionEnum;
use App\Entity\UserVideoAction;
use App\Entity\UserVideoRating;
use App\Entity\Video;
use App\Exception\ApiException;
use App\Form\RatingType;
use App\Form\VideoType;
use App\Repository\UserVideoActionRepository;
use App\Repository\UserVideoRatingRepository;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/video')]
class VideoController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use NormalizeWithVoterPermissionsTrait;
    use TranslatorTrait;
    use ValidateSaveUserActionAndReturnResponseTrait;

    public function __construct(
        private VideoRepository $videoRepository,
        private FileHandlerInterface $fileHandler,
        private UserVideoRatingRepository $userVideoRatingRepository,
        private UserVideoActionRepository $userVideoActionRepository,
    ) {}

    #[Route('/', name: 'api_video_index', methods: 'GET')]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'data' => $this->normalizeWithVoterPermissions(
                $this->videoRepository->findAll(), 
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
        if ($this->getUser()) {
            $data['currentUserRating'] = $video->getUserRating($this->getUser());
        }

        return $this->json([
            'data' => $data,
        ]);
    }

    #[Route('{_</?>}', name: 'api_video_create', methods: 'POST')]
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
        $this->em->flush();
        
        $this->fileHandler->deleteVideo($video->getVideoPath());
        $this->fileHandler->deleteImage($video->getSnapshotPath());
        
        return $this->json([
            'message' => $this->translator->trans('controller.video.delete.success'),
            'data' => $this->getNormalizedExtendedVideoWithPermissions($video),
        ]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/rate', name: 'api_video_rate', methods: 'POST')]
    public function rate(Video $video, Request $request): JsonResponse
    {
        $content = $this->getJsonContentOrThrowApiException($request);
        $newRating = new UserVideoRating();
        $form = $this->createForm(RatingType::class, $newRating);
        
        $form->submit($content);
        if ($form->isValid()) {
            $oldRating = $this->userVideoRatingRepository->findOneBy([
                'user' => $this->getUser(), 
                'video' => $video,
            ]);

            $existingRating = null;
            $oldRate = null;

            if ($oldRating) {
                $oldRate = $oldRating->getRating();

                $this->em->remove($oldRating);
                $this->em->flush();
            }
            
            if ($oldRate !== $newRating->getRating()) {
                $newRating->setUser($this->getUser());
                $newRating->setVideo($video);

                $this->em->persist($newRating);
                $this->em->flush();

                $existingRating = $newRating;
            }

            return $this->json(
                [
                    'data' => ['rating' => $existingRating?->getRating()],
                    'message' => $this->translator->trans('controller.video.rate.success'),
                ],
                context: ['groups' => ['rating']]
            );
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/report', name: 'api_video_report', methods: 'POST')]
    public function report(Video $video, Request $request): JsonResponse
    {
        return $this->validateSaveUserActionAndReturnResponse(
            UserActionEnum::REPORT, 
            ['video' => $video, 'user' => $this->getUser()],
            UserVideoAction::class,
            $request, 
            ['reason' => $this->getUserActionReasonConstraints()], 
            $this->translator->trans('controller.video.report.success')
        );
    }

    #[ParamConverter('video', class: Video::class)]
    #[Route('/{id}/report', name: 'api_video_report_delete', methods: 'DELETE')]
    public function reportDelete(Video $video, Request $request): JsonResponse
    {
        $this->userVideoActionRepository->deleteBy([
            'video' => $video, 
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
            ['groups' => ['video_extended', 'user', 'tag', 'rating', 'user_action']],
            [
                'currentUserRating' => ['getUserRating', [$this->getUser()]],
                'currentUserActions' => ['getUserActions', [$this->getUser()]],
            ]
        );
    }
}
