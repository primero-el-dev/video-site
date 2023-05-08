<?php

namespace App\Controller\Api;

use App\Controller\Api\Traits\ValidateSaveUserActionAndReturnResponseTrait;
use App\Entity\Comment;
use App\Entity\Enum\UserActionEnum;
use App\Entity\UserCommentAction;
use App\Entity\UserCommentRating;
use App\Exception\ApiException;
use App\Form\RatingType;
use App\Repository\CommentRepository;
use App\Repository\UserCommentActionRepository;
use App\Repository\UserCommentRatingRepository;
use App\Security\Voter\CommentVoter;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use App\Traits\NormalizeWithVoterPermissionsTrait;
use App\Traits\TranslatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/comment')]
class CommentController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use NormalizeWithVoterPermissionsTrait;
    use TranslatorTrait;
    use ValidateSaveUserActionAndReturnResponseTrait;

    public function __construct(
        private CommentRepository $commentRepository,
        private UserCommentRatingRepository $userCommentRatingRepository,
        private UserCommentActionRepository $userCommentActionRepository,
    ) {}

    #[Route('/', name: 'api_comment_index', methods: 'GET')]
    public function index(Request $request): JsonResponse
    {
        // We get all and signal on page that comments may be deleted
        $this->em->getFilters()->disable('softdeleteable');

        $params = [];
        $addParam = function ($param) use($request, &$params) {
            if ($p = $request->query->get($param)) {
                $params[$param] = $p;
            }
        };
        
        $addParam('video');
        $addParam('parent');
        $addParam('owner');
        $addParam('content');
        $addParam('order');
        $addParam('limit');
        $addParam('minid');
        $addParam('maxid');
        
        return $this->json([
            'data' => $this->getNormalizedExtendedCommentWithPermissions(
                $this->commentRepository->findByConstraints($params)
            ),
        ]);
    }

    #[Route('/', name: 'api_comment_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('USER_VERIFIED');
        
        $content = $this->getJsonContentOrThrowApiException($request);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->submit($content);
        if ($form->isValid()) {
            $comment->setOwner($this->getUser());
            if ($parent = $comment->getParent()) {
                $comment->setVideo($parent->getVideo());
            }

            $this->commentRepository->save($comment, true);

            return $this->json([
                'message' => $this->translator->trans('controller.comment.create.success'),
                'data' => $this->getNormalizedExtendedCommentWithPermissions($comment),
            ]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}', name: 'api_comment_update', methods: 'PUT')]
    public function update(Comment $comment, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(CommentVoter::UPDATE, $comment);
        
        $content = $this->getJsonContentOrThrowApiException($request);
        $form = $this->createForm(CommentType::class, $comment, ['update_mode' => true]);

        $form->submit($content);
        if ($form->isValid()) {
            $this->commentRepository->save($comment, true);

            return $this->json([
                'message' => $this->translator->trans('controller.comment.update.success'),
                'data' => $this->getNormalizedExtendedCommentWithPermissions($comment),
            ]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}', name: 'api_comment_delete', methods: 'DELETE')]
    public function delete(Comment $comment, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

        $this->em->remove($comment);
        $this->em->flush();

        return $this->json([
            'message' => $this->translator->trans('controller.comment.delete.success'),
            'data' => $this->getNormalizedExtendedCommentWithPermissions($comment),
        ]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}/rate', name: 'api_comment_rate', methods: 'POST')]
    public function rate(Comment $comment, Request $request): JsonResponse
    {
        $content = $this->getJsonContentOrThrowApiException($request);
        $newRating = new UserCommentRating();
        $form = $this->createForm(RatingType::class, $newRating);
        
        $form->submit($content);
        if ($form->isValid()) {
            $oldRating = $this->userCommentRatingRepository->findOneBy([
                'user' => $this->getUser(), 
                'comment' => $comment,
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
                $newRating->setComment($comment);

                $this->em->persist($newRating);
                $this->em->flush();

                $existingRating = $newRating;
            }

            return $this->json(
                [
                    'data' => ['rating' => $existingRating?->getRating()],
                    'message' => $this->translator->trans('controller.comment.rate.success'),
                ],
                context: ['groups' => ['rating']]
            );
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}/report', name: 'api_comment_report', methods: 'POST')]
    public function report(Comment $comment, Request $request): JsonResponse
    {
        return $this->validateSaveUserActionAndReturnResponse(
            UserActionEnum::REPORT, 
            ['comment' => $comment, 'user' => $this->getUser()],
            UserCommentAction::class,
            $request, 
            ['reason' => $this->getUserActionReasonConstraints()], 
            $this->translator->trans('controller.comment.report.success')
        );
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}/report', name: 'api_comment_report_delete', methods: 'DELETE')]
    public function reportDelete(Comment $comment, Request $request): JsonResponse
    {
        $this->userCommentActionRepository->deleteBy([
            'comment' => $comment, 
            'user' => $this->getUser(),
            'action' => UserActionEnum::REPORT,
        ]);
        
        return $this->json([
            'data' => null,
            'message' => $this->translator->trans('controller.comment.reportDelete.success'),
        ]);
    }

    /**
     * @return Constraint[]
     */
    private function getUserActionReasonConstraints(): array
    {
        return [
            new Assert\NotBlank(message: 'entity.userCommentAction.additionalInfo.reason.notBlank'),
            new Assert\Length(
                max: 255,
                maxMessage: 'entity.userCommentAction.additionalInfo.reason.length.max',
            ),
        ];
    }

    private function getNormalizedExtendedCommentWithPermissions(Comment|array $comment): array
    {
        return $this->normalizeWithVoterPermissions(
            $comment, 
            new CommentVoter(), 
            ['groups' => ['comment', 'user', 'timestamp', 'rating', 'is_deleted']],
            [
                'currentUserRating' => ['getUserRating', [$this->getUser()]],
                'currentUserActions' => ['getUserActions', [$this->getUser()]],
            ]
        );
    }
}
