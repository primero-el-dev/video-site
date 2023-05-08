<?php

namespace App\Validator;

use App\Entity\Tag;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TagValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\Tag $constraint */
        if (!$value instanceof Tag 
            || !$value->getName() 
            || !preg_match('/^[a-z\d_\.\-]+$/', $value->getName())) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->getName())
                ->setParameter('{{ limit }}', $constraint->maxLength)
                ->addViolation();
        }
    }
}
