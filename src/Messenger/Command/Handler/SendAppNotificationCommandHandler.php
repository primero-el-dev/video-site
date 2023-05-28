<?php

namespace App\Messenger\Command\Handler;

use App\Entity\Enum\UserActionEnum;
use App\Messenger\Command\Handler\CommandHandlerInterface;
use App\Messenger\Command\SendAppNotificationCommand;
use App\Notification\NotificationManagerInterface;
use App\Repository\UserRepository;
use App\Traits\EntityManagerTrait;

class SendAppNotificationCommandHandler implements CommandHandlerInterface
{
    use EntityManagerTrait;

    public function __construct(
        private NotificationManagerInterface $notificationManager,
        private UserRepository $userRepository,
    ) {}

    public function __invoke(SendAppNotificationCommand $command): void
    {
        $this->em->getFilters()->disable('softdeleteable');
        $user = $this->userRepository->find($command->userId);
        $subject = $this->em->getRepository($command->subjectClass)->find($command->subjectId);
        $this->em->getFilters()->enable('softdeleteable');

        if ($subject) {
            $this->notificationManager->processAndNotify($user, UserActionEnum::from($command->action), $subject);
        }
    }
}