<?php

namespace App\Validator;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CurrentUserPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\CurrentUserPassword $constraint */
        if (!$user = $this->security->getUser()) {
            return;
        }

        if (!$this->passwordHasher->isPasswordValid($user, (string) $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
