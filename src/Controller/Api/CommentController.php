<?php

namespace App\Controller\Api;

use App\Controller\Api\Traits\ValidateProcessAndReturnExistingTrait;
use App\Entity\Comment;
use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Exception\ApiException;
use App\Messenger\Command\SendAppNotificationCommand;
use App\Repository\CommentRepository;
use App\Repository\UserSubjectAction\UserCommentActionRepository;
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
use Symfony\Component\Messenger\MessageBusInterface;
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
    use ValidateProcessAndReturnExistingTrait;

    public function __construct(
        private CommentRepository $commentRepository,
        private UserCommentActionRepository $userCommentActionRepository,
        private MessageBusInterface $commandBus,
    ) {}

    #[Route('/', name: 'api_comment_index', methods: 'GET')]
    public function index(Request $request): JsonResponse
    {
        // We get all and signal on page that comments may be deleted
        $this->em->getFilters()->disable('softdeleteable');

        return $this->json([
            'data' => $this->getNormalizedExtendedCommentWithPermissions(
                $this->commentRepository->findByConstraints($request->query->all())
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

            $this->dispatchAppNotificationCommand(UserActionEnum::COMMENT_CREATE, $comment->getVideo());
            if ($comment->getParent()) {
                $this->dispatchAppNotificationCommand(UserActionEnum::COMMENT_CREATE, $comment->getParent());
            }

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

        $this->dispatchAppNotificationCommand(UserActionEnum::COMMENT_DELETE, $comment->getVideo());
        if ($comment->getParent()) {
            $this->dispatchAppNotificationCommand(UserActionEnum::COMMENT_DELETE, $comment->getParent());
        }

        return $this->json([
            'message' => $this->translator->trans('controller.comment.delete.success'),
            'data' => $this->getNormalizedExtendedCommentWithPermissions($comment),
        ]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}/{rate<(like)|(dislike)>}', name: 'api_comment_rate', methods: 'POST')]
    public function rate(Comment $comment, string $rate, Request $request): JsonResponse
    {
        $entity = $this->validateProcessAndReturnExisting(
            UserActionEnum::from($rate), 
            $this->getUser(),
            $comment,
            $request, 
        );

        return $this->json([
            'data' => $entity?->getAction(),
            'message' => $this->translator->trans('controller.comment.rate.success'),
        ]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}/report', name: 'api_comment_report', methods: 'POST')]
    public function report(Comment $comment, Request $request): JsonResponse
    {
        $entity = $this->validateProcessAndReturnExisting(
            UserActionEnum::REPORT, 
            $this->getUser(),
            $comment,
            $request, 
            ['reason' => $this->getUserActionReasonConstraints()], 
        );

        return $this->json([
            'data' => $entity?->getAction(),
            'message' => $this->translator->trans('controller.comment.report.success'),
        ]);
    }

    #[ParamConverter('comment', class: Comment::class)]
    #[Route('/{id}/report', name: 'api_comment_report_delete', methods: 'DELETE')]
    public function reportDelete(Comment $comment, Request $request): JsonResponse
    {
        $this->userCommentActionRepository->deleteBy([
            'subject' => $comment, 
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
            ['groups' => ['comment', 'user', 'timestamp', 'rating', 'is_deleted', 'user_action', 'additional_data']],
            [
                'currentUserRating' => ['getUserRating', [$this->getUser()]],
                'currentUserActions' => ['getUserActions', [$this->getUser()]],
            ]
        );
    }

    private function dispatchAppNotificationCommand(UserActionEnum $action, EntityInterface $entity): void
    {
        $this->commandBus->dispatch(new SendAppNotificationCommand(
            $this->getUser(),
            $action,
            $entity
        ));
    }
}
