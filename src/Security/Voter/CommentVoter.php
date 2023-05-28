<?php

namespace App\Security\Voter;

use App\Util\EntityUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter implements HasPermissionsInterface
{
    public const UPDATE = 'COMMENT_UPDATE';
    public const DELETE = 'COMMENT_DELETE';

    public const PERMISSIONS = [self::UPDATE, self::DELETE];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::PERMISSIONS)
            && $subject instanceof \App\Entity\Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::UPDATE => $this->isOwnedByCurrentUser($subject, $token),
            self::DELETE => $this->isOwnedByCurrentUser($subject, $token),
            default => false,
        };
    }

    private function isOwnedByCurrentUser(mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        return $user 
            && ($user->hasOneOfRoles(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']) 
            || EntityUtil::areSame($user, $subject->getOwner()));
    }
}
