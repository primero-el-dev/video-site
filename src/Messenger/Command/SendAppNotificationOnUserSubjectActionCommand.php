<?php

namespace App\Messenger\Command;

use App\Messenger\AsyncInterface;
use App\Messenger\Command\CommandInterface;

class SendAppNotificationOnUserSubjectActionCommand implements CommandInterface//, AsyncInterface
{
    public function __construct(public readonly string $userSubjectActionId) {}
}