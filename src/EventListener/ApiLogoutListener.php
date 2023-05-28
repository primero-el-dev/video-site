<?php

namespace App\EventListener;

use App\Traits\TranslatorTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class ApiLogoutListener implements EventSubscriberInterface
{
    use TranslatorTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $event->setResponse(new JsonResponse([
            'message' => $this->translator->trans('controller.login.logout.success'),
        ]));
    }
}