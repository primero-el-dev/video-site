<?php

namespace App\Messenger\Command;

use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\User;
use App\Messenger\AsyncInterface;
use App\Messenger\Command\CommandInterface;

class SendAppNotificationCommand implements CommandInterface//, AsyncInterface
{
    public readonly string $userId;
    public readonly string $action;
    public readonly string|int $subjectId;
    public readonly ?string $subjectClass;

    public function __construct(User $user, UserActionEnum $userAction, EntityInterface $subject)
    {
        $this->userId = $user->getId();
        $this->action = $userAction->value;
        $this->subjectId = $subject->getId();
        $this->subjectClass = get_class($subject);
    }
}