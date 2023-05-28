<?php

namespace App\Security\Voter;

use App\Entity\HasOwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const ANONYMOUS = 'USER_ANONYMOUS';
    public const VERIFIED = 'USER_VERIFIED';
    public const OWNED = 'USER_OWNED';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ANONYMOUS, self::VERIFIED, self::OWNED]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        return match($attribute) {
            self::ANONYMOUS => !$user,
            self::VERIFIED => $user && $user->isVerified(),
            self::OWNED => $user 
                && $subject instanceof HasOwnerInterface 
                && $subject->getOwner()?->getId() === $user->getId(),
            default => false,
        };
    }
}
