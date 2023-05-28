<?php

namespace App\ParamConverter;

use App\Entity\Token\Token;
use App\Traits\EntityManagerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ValidTokenParamConverter implements ParamConverterInterface
{
    use EntityManagerTrait;

    public function supports(ParamConverter $configuration): bool
    {
        return is_a($configuration->getClass(), Token::class, true);
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $tokenClass = $configuration->getClass();
        $value = $request->query->get($configuration->getName());
        $token = $this->em->getRepository($tokenClass)->findOneValidByValue((string) $value);

        $request->attributes->set($configuration->getName(), $token);

        return (bool) $token;
    }
}