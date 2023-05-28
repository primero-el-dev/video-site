<?php

namespace App\Security;

use App\Traits\SerializerTrait;
use App\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonLoginAuthenticator extends AbstractAuthenticator
{
    use SerializerTrait;
    use TranslatorTrait;

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'api_login';
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate(Request $request): Passport
    {
        $content = json_decode($request->getContent(), true);
        if (!$content) {
            throw new AuthenticationException($this->translator->trans('common.invalidBodyFormat'));
        }

        $rememberMeBagde = new RememberMeBadge();
        if (!empty($content['rememberMe'])) {
            $rememberMeBagde->enable();
        }

        return new Passport(
            new UserBadge($content['email'] ?? ''),
            new PasswordCredentials($content['password'] ?? ''),
            [
                $rememberMeBagde,
            ]
        );
    }
    
    public function onAuthenticationSuccess(
        Request $request, 
        TokenInterface $token, 
        string $firewallName
    ): ?JsonResponse {
        return new JsonResponse([
            'message' => $this->translator->trans('security.jsonLoginAuthenticator.success'),
            'data' => $this->serializer->normalize($token->getUser(), 'json', ['groups' => ['user']]),
        ]);
    }

    public function onAuthenticationFailure(
        Request $request, 
        AuthenticationException $exception
    ): ?JsonResponse {
        $error = $_ENV['APP_ENV'] === 'prod' 
            ? $this->translator->trans('security.jsonLoginAuthenticator.failure')
            : strtr($exception->getMessageKey(), $exception->getMessageData());
        
        return new JsonResponse(['error' => $error], 401);
    }
}
