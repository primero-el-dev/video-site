<?php

namespace App\EventListener;

use App\Util\ArrayUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class ResponseListener implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private string $rememberMeCookieName,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', -8],
        ];
    }

    /**
     * @throws AccessDeniedException
     */
    public function onResponse(ResponseEvent $event): void
    {
        if ($rememberMeExpiry = $this->getCookieExpiry($event, $this->rememberMeCookieName)) {
            $this->setResponseCookie($event, 'REMEMBER-ME-EXPIRY', $rememberMeExpiry);
        }

        $lifetime = ($this->security->getUser()) 
            ? (time() + session_get_cookie_params()['lifetime']) 
            : 0;
        $this->setResponseCookie($event, 'SESSION-EXPIRY', $lifetime);
    }

    private function setResponseCookie(ResponseEvent $event, string $name, int $lifetime): void
    {
        $cookie = new Cookie(
            name: $name, 
            value: $lifetime, 
            expire: $lifetime, 
            path: '/', 
            domain: null, 
            secure: false, 
            httpOnly: false, 
            raw: false, 
            sameSite: 'strict'
        );
        $event->getResponse()->headers->setCookie($cookie);
    }

    private function getCookieExpiry(ResponseEvent $event, string $cookieName): ?int
    {
        $cookies = ArrayUtil::flatten($event->getResponse()->headers->getCookies());
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return $cookie->getExpiresTime();
            }
        }

        return null;
    }
}