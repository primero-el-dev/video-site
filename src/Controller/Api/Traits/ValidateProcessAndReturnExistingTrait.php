<?php

namespace App\Controller\Api\Traits;

use App\Entity\AdditionalData;
use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\User;
use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Exception\ApiException;
use App\Form\DynamicType;
use App\Messenger\Command\SendAppNotificationCommand;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraint;
use Exception;

trait ValidateProcessAndReturnExistingTrait
{
    use EntityManagerTrait;
    use GetJsonContentOrThrowApiException;
    use GetFieldFirstFormErrorMapTrait;

    public function __construct(private MessageBusInterface $commandBus) {}

    /**
     * Validate request, create/update/delete user action and based on UserActionEnum's config and return current
     * 
     * @param array<string, Constraint[]> $additionalFieldsConstraints
     * @throws ApiException
     * @throws Exception
     */
    public function validateProcessAndReturnExisting(
        UserActionEnum $action, 
        User $user,
        EntityInterface $subject,
        Request $request, 
        array $additionalFieldsConstraints = []
    ): ?UserSubjectAction {
        $class = UserSubjectAction::getSubclassByEntity($subject);
        if (!$class) {
            throw new Exception(sprintf(
                "'%s' hasn't mapped an instance of '%s'.",
                get_class($subject),
                UserSubjectAction::class
            ));
        }

        $actionEntity = (new $class())
            ->setUser($user)
            ->setSubject($subject)
            ->setAction($action);

        if ($additionalFieldsConstraints) {
            $content = $this->getJsonContentOrThrowApiException($request);
            $form = $this->createForm(DynamicType::class, null, [
                'fields' => $additionalFieldsConstraints,
            ]);
            
            $form->submit($content);
            if (!$form->isValid()) {
                throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
            } else {
                $data = [];
                foreach (array_keys($additionalFieldsConstraints) as $field) {
                    $data[$field] = $form->get($field)->getData();
                }

                $additionalData = (new AdditionalData())->setContent($data);
                $actionEntity->setAdditionalData($additionalData);
            }
        }

        if ($actionEntity::isActionToggleable($action)) {
            return $actionEntity::getConflictingActions($action)
                ? $this->handleToggleableAndConflicted($user, $actionEntity)
                : $this->handleToggleableAndNotConflicted($user, $actionEntity);
        } else {
            return $actionEntity::getConflictingActions($action)
                ? $this->handleNotToggleableAndConflicted($user, $actionEntity)
                : $this->handleNotToggleableAndNotConflicted($user, $actionEntity);
        }
    }

    private function handleToggleableAndConflicted(User $user, UserSubjectAction $newAction): ?UserSubjectAction
    {
        $oldAction = $this->em->getRepository($newAction::class)->findByValuesLists([
            'user' => $newAction->getUser(), 
            'subject' => $newAction->getSubject(),
            'action' => $newAction::getConflictingActions($newAction->getAction(), true),
        ])[0] ?? null;

        $old = null;

        if ($oldAction) {
            $old = $oldAction->getAction();

            $this->em->remove($oldAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $old->getReverse(), 
                $newAction->getSubject()
            ));
        }
        
        if ($old !== $newAction->getAction()) {
            $this->em->persist($newAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $newAction->getAction(), 
                $newAction->getSubject()
            ));

            return $newAction;
        }

        return null;
    }

    private function handleToggleableAndNotConflicted(User $user, UserSubjectAction $newAction): ?UserSubjectAction
    {
        $oldAction = $this->em->getRepository($newAction::class)->findOneBy([
            'user' => $newAction->getUser(), 
            'subject' => $newAction->getSubject(),
            'action' => $newAction->getAction(),
        ]);

        if ($oldAction) {
            $this->em->remove($oldAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $newAction->getAction()->getReverse(), 
                $newAction->getSubject()
            ));

            return null;
        } else {
            $this->em->persist($newAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $newAction->getAction(), 
                $newAction->getSubject()
            ));

            return $newAction;
        }
    }

    private function handleNotToggleableAndNotConflicted(User $user, UserSubjectAction $newAction): ?UserSubjectAction
    {
        $oldAction = $this->em->getRepository($newAction::class)->findOneBy([
            'user' => $newAction->getUser(), 
            'subject' => $newAction->getSubject(),
            'action' => $newAction->getAction(),
        ]);

        if ($oldAction) {
            return $oldAction;
        } else {
            $this->em->persist($newAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $newAction->getAction(), 
                $newAction->getSubject()
            ));

            return $newAction;
        }
    }

    private function handleNotToggleableAndConflicted(User $user, UserSubjectAction $newAction): ?UserSubjectAction
    {
        $oldAction = $this->em->getRepository($newAction::class)->findByValuesLists([
            'user' => $newAction->getUser(), 
            'subject' => $newAction->getSubject(),
            'action' => $newAction::getConflictingActions($newAction->getAction(), true),
        ])[0] ?? null;

        if ($oldAction) {
            $oldActionEnum = $oldAction->getAction();
            $oldAction->setAction($newAction->getAction());

            $this->em->persist($oldAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $oldActionEnum->getReverse(), 
                $newAction->getSubject()
            ));

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $newAction->getAction(), 
                $newAction->getSubject()
            ));

            return $oldAction;
        } else {
            $this->em->persist($newAction);
            $this->em->flush();

            $this->commandBus->dispatch(new SendAppNotificationCommand(
                $user,
                $newAction->getAction(), 
                $newAction->getSubject()
            ));

            return $newAction;
        }
    }
}