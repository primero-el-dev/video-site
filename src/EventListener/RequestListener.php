<?php

namespace App\EventListener;

use App\Util\ArrayUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class RequestListener implements EventSubscriberInterface
{
    public function __construct(private Security $security) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['setUserIdInSession', 0],
        ];
    }

    public function setUserIdInSession(RequestEvent $event): void
    {
        if ($user = $this->security->getUser()) {
            $event->getRequest()->getSession()->set($_ENV['USER_ID_SESSION_KEY'], (string) $user->getId());
        }
    }
}