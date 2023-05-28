<?php

namespace App\Messenger\Command;

use App\Entity\User;
use App\Messenger\Command\SendTemplatedEmailCommand;

class SendResetPasswordEmailCommand extends SendTemplatedEmailCommand
{
    public function __construct(User $user, string $link)
    {
        parent::__construct(
            [(string) $user->getId()], 
            ['email.resetPassword.subject', []], 
            'email/reset_password.html.twig',
            false,
            ['link' => $link],
        );
    }
}