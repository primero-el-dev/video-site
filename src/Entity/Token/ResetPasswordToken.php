<?php

namespace App\Entity\Token;

use App\Entity\Token\Token;
use App\Entity\User;
use App\Repository\Token\ResetPasswordTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: ResetPasswordTokenRepository::class)]
class ResetPasswordToken extends Token
{
    public function __construct(User $owner)
    {
        $this->owner = $owner;
        $this->expiry = new DateTime('+120 minutes');
        $this->value = bin2hex(random_bytes(50));
    }
}