<?php

namespace App\Messenger\Command;

use App\Entity\User;
use App\Messenger\Command\SendTemplatedEmailCommand;

class SendRegistrationConfirmationEmailCommand extends SendTemplatedEmailCommand
{
    public function __construct(User $user, string $link)
    {
        parent::__construct(
            [(string) $user->getId()], 
            ['email.registrationConfirmation.subject', []], 
            'email/registration_confirmation.html.twig',
            false,
            ['link' => $link],
        );
    }
}