<?php

namespace App\Messenger\Command\Handler;

use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Notification\Notification;
use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Messenger\Command\Handler\CommandHandlerInterface;
use App\Messenger\Command\SendAppNotificationOnUserSubjectActionCommand;
use App\Notification\NotificationManagerInterface;
use App\Traits\EntityManagerTrait;

class SendAppNotificationOnUserSubjectActionCommandHandler implements CommandHandlerInterface
{
    use EntityManagerTrait;

    public function __construct(private NotificationManagerInterface $notificationManager) {}

    public function __invoke(SendAppNotificationOnUserSubjectActionCommand $command): void
    {
        $userSubjectAction = $this->em
            ->getRepository(UserSubjectAction::class)
            ->find($command->userSubjectActionId);
        $subject = $userSubjectAction->getSubject();
        $action = $userSubjectAction->getAction();
        $notifyUser = ($subject instanceof HasOwnerInterface) ? $subject->getOwner() : $subject;
        $actionCount = $subject->getActionsCount($action);

        $notificationClass = Notification::getSubclassByEntity($subject);
        $notifications = $this->em->getRepository($notificationClass)->findBy([
            'action' => $action->value,
            'subject' => $subject,
        ]);

        if (!$notifications) {
            $notification = (new $notificationClass())
                ->setUser($notifyUser)
                ->setSubject($subject)
                ->setAction($action->value)
                ->setCount($actionCount);
            
            $notifications = [$notification];

            // if ($action === UserActionEnum::REPORT) {
            //     $admins = $this->em->getRepository(User::class)->findBy([
            //         'action' => $action->value,
            //         'subject' => $subject,
            //     ]);
            // }
        }

        foreach ($notifications as $notification) {
            $notification
                ->setCount($actionCount)
                ->setSeen(false);
            
            if ($userSubjectAction->getAdditionalData()) {
                $notification->addUserSubjectAction($userSubjectAction);
            }

            $this->em->persist($notification);
        }

        $this->em->flush();

        $this->notificationManager->notify([$notifyUser], $notifications);
    }
}