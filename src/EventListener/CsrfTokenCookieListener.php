<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * This class acts as middleware
 */
class CsrfTokenCookieListener implements EventSubscriberInterface
{
    private const TOKEN_INTENTION = 'csrf_token';
    private const TOKEN_COOKIE_NAME = 'XSRF-TOKEN';

    public function __construct(private CsrfTokenManagerInterface $tokenManager) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['validateOnApiRequest', 8],
            KernelEvents::RESPONSE => ['refreshTokenOnResponse', -9],
        ];
    }

    /**
     * @throws AccessDeniedException
     */
    public function validateOnApiRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (str_starts_with($event->getRequest()->getRequestUri(), '/api')) {
            $token = new CsrfToken(self::TOKEN_INTENTION, $request->cookies->get(self::TOKEN_COOKIE_NAME));
            if (!$this->tokenManager->isTokenValid($token)) {
                throw new AccessDeniedException('Invalid CSRF token.');
            }
        }
    }

    public function refreshTokenOnResponse(ResponseEvent $event): void
    {
        $token = $this->tokenManager->getToken(self::TOKEN_INTENTION);
        $cookie = new Cookie(self::TOKEN_COOKIE_NAME, $token->getValue(), time() + 3600, '/', null, false, true, false, 'strict');
        $event->getResponse()->headers->setCookie($cookie);
    }
}